<?php

namespace App\Services;

use App\Mail\RegistrationVerifiedMail;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Midtrans Snap integration for the registration flow.
 *
 * A paid registration gets a Snap transaction right after it is stored; the returned
 * redirect_url is the hosted payment page the participant is sent to. When Midtrans
 * settles a payment it calls the notification endpoint, whose signature is verified here.
 */
class MidtransService
{
    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.server_key'));
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    /**
     * Snap transaction endpoint: sandbox by default so test payments never move real money.
     */
    public function snapEndpoint(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Creates the Snap transaction for a paid registration and remembers the token and
     * redirect URL. Returns the payment page URL, or null when the package is free,
     * Midtrans is not configured, or the API call fails (the caller then rolls the
     * registration back, since there is no other way to pay).
     */
    public function createPayment(Registration $registration, bool $retry = false): ?string
    {
        $price = (int) ($registration->package?->price ?? 0);

        if ($price <= 0 || ! $this->isConfigured()) {
            return null;
        }

        $serverKey = (string) config('services.midtrans.server_key');

        // Midtrans never accepts an order_id twice, so a new attempt gets a suffix
        $orderId = $retry
            ? $registration->registration_number.'-R'.Str::lower(Str::random(5))
            : $registration->registration_number;

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price,
            ],
            'item_details' => [[
                'id' => (string) $registration->package_id,
                'price' => $price,
                'quantity' => 1,
                'name' => mb_substr(trim(($registration->package?->name ?? 'Paket').' '.($registration->category?->name ?? '')), 0, 50),
            ]],
            'customer_details' => array_filter([
                'first_name' => $registration->participant?->full_name,
                'email' => $registration->participant?->email,
                'phone' => $registration->participant?->phone,
            ]),
            'callbacks' => [
                // Snap sends the participant back here after paying; the page waits for the
                // notification when it arrives a little later, then reveals the participant number.
                // Built from the request host so a tunnel (ngrok) keeps working without changing APP_URL.
                'finish' => $this->finishUrl($registration),
                'unfinish' => $this->finishUrl($registration),
                'error' => $this->finishUrl($registration),
            ],
            'expiry' => ['unit' => 'hours', 'duration' => 24],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($this->snapEndpoint(), $payload);

            // Number already used by an earlier transaction (a number freed by a rolled-back or deleted
            // registration, or leftovers in the Midtrans account): retry once under a fresh order_id.
            // Midtrans words the error in English or Indonesian depending on the account.
            if ($response->status() === 400 && preg_match('/order_id.*(already been taken|sudah digunakan)/i', (string) json_encode($response->json('error_messages')))) {
                $orderId = $registration->registration_number.'-R'.Str::lower(Str::random(5));
                $payload['transaction_details']['order_id'] = $orderId;

                $response = Http::withBasicAuth($serverKey, '')
                    ->acceptJson()
                    ->asJson()
                    ->timeout(15)
                    ->post($this->snapEndpoint(), $payload);
            }

            if ($response->failed()) {
                Log::error('Midtrans Snap ditolak.', [
                    'registration' => $registration->registration_number,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return null;
            }

            $token = (string) $response->json('token');
            $redirectUrl = (string) $response->json('redirect_url');

            if ($token === '' || $redirectUrl === '') {
                Log::error('Midtrans Snap tidak mengembalikan token/redirect_url.', [
                    'registration' => $registration->registration_number,
                ]);

                return null;
            }

            $registration->forceFill([
                'snap_token' => $token,
                'snap_redirect_url' => $redirectUrl,
                'payment_order_id' => $orderId,
                'payment_expires_at' => now()->addHours(24),
            ])->save();

            return $redirectUrl;
        } catch (Throwable $exception) {
            Log::error('Midtrans Snap gagal dihubungi: '.$exception->getMessage(), [
                'registration' => $registration->registration_number,
            ]);

            return null;
        }
    }

    /**
     * Page Snap returns to after checkout. Always built from APP_URL: the participant may pay on a
     * phone, where an address built from the request host (127.0.0.1 behind ngrok) would not open.
     */
    private function finishUrl(Registration $registration): string
    {
        return url('/pembayaran/selesai/'.urlencode($registration->registration_number));
    }

    /**
     * Core API transaction status: api.midtrans.com (production) / api.sandbox.midtrans.com.
     */
    public function statusEndpoint(string $orderId): string
    {
        $base = $this->isProduction()
            ? 'https://api.midtrans.com/v2/'
            : 'https://api.sandbox.midtrans.com/v2/';

        return $base.urlencode($orderId).'/status';
    }

    /**
     * Asks Midtrans for the transaction status directly. Used while the participant waits on the
     * finish page, so the registration is marked paid even when the HTTP notification has not
     * arrived yet (or the dashboard notification URL is not configured).
     *
     * @return array{registration: Registration, became_paid: bool}|null
     */
    public function syncStatus(Registration $registration): ?array
    {
        if (! $this->isConfigured() || $registration->payment_status !== 'unpaid' || blank($registration->snap_token)) {
            return null;
        }

        try {
            $response = Http::withBasicAuth((string) config('services.midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(10)
                ->get($this->statusEndpoint($this->orderId($registration)));

            $payload = $response->json();

            // Before the participant starts paying, the Core API answers 404 "Transaction not found"
            if (! is_array($payload) || ($payload['order_id'] ?? '') !== $this->orderId($registration)) {
                return null;
            }

            return $this->applyNotification($payload);
        } catch (Throwable $exception) {
            Log::warning('Midtrans status check gagal: '.$exception->getMessage(), [
                'registration' => $registration->registration_number,
            ]);

            return null;
        }
    }

    /**
     * Midtrans signs every notification with sha512(order_id + status_code + gross_amount + server_key).
     */
    public function notificationIsValid(array $payload): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($serverKey === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha512', implode('', [
            $payload['order_id'] ?? '',
            $payload['status_code'] ?? '',
            $payload['gross_amount'] ?? '',
            $serverKey,
        ]));

        return hash_equals($expected, $signature);
    }

    /**
     * Applies a verified notification inside a row lock, so the webhook and the finish-page
     * polling can never both treat the same payment as new. Safe to run more than once.
     *
     * - settlement/capture marks the registration paid (any attempt of that registration counts,
     *   money received is never ignored). A registration cancelled by an admin stays cancelled.
     * - expire/cancel/deny/failure of the CURRENT attempt releases the registration (status
     *   cancelled, so its quota seat is freed) and lets the participant pay again.
     *
     * @return array{registration: Registration, became_paid: bool}|null
     */
    public function applyNotification(array $payload): ?array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $number = $this->registrationNumber($orderId);

        return DB::transaction(function () use ($payload, $orderId, $number) {
            $registration = Registration::where('registration_number', $number)->lockForUpdate()->first();

            if (! $registration) {
                return null;
            }

            $transactionStatus = (string) ($payload['transaction_status'] ?? '');
            $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');
            $settled = in_array($transactionStatus, ['capture', 'settlement'], true)
                && ! in_array($fraudStatus, ['deny', 'challenge'], true);
            $isCurrent = $this->orderId($registration) === $orderId;
            $becamePaid = false;

            if ($settled && $registration->payment_status !== 'paid') {
                // Cancelled by the lapse of this very payment is revived; cancelled by an admin is not
                $revive = $registration->registration_status !== 'cancelled' || $registration->payment_expired_at !== null;

                $registration->payment_status = 'paid';
                $registration->payment_method = $payload['payment_type'] ?? $registration->payment_method;
                $registration->paid_at = now();
                $registration->verified_at = now();
                $registration->payment_expired_at = null;

                if ($revive) {
                    $registration->registration_status = 'confirmed';
                    $becamePaid = true;
                } else {
                    Log::warning('Pembayaran masuk untuk pendaftaran yang dibatalkan panitia, perlu ditinjau manual.', [
                        'registration' => $registration->registration_number,
                    ]);
                }
            } elseif (! $settled && $isCurrent) {
                $registration->payment_method = $payload['payment_type'] ?? $registration->payment_method;

                if (in_array($transactionStatus, ['expire', 'cancel', 'deny', 'failure'], true)) {
                    $this->release($registration);
                }
            }

            $registration->save();

            return ['registration' => $registration->fresh(['participant', 'category', 'package', 'event']), 'became_paid' => $becamePaid];
        });
    }

    /**
     * Frees the seat of an unpaid registration whose payment lapsed. Keeps the registration so the
     * participant can pay again (see retryPayment); does nothing for paid, free or admin-cancelled ones.
     */
    private function release(Registration $registration): void
    {
        if ($registration->payment_status === 'unpaid' && $registration->registration_status === 'pending') {
            $registration->registration_status = 'cancelled';
            $registration->payment_expired_at = now();
        }
    }

    /**
     * Cancels every unpaid registration whose Snap page has lapsed. Midtrans is asked first, so a
     * payment that went through without a notification is still credited. Returns the number released.
     */
    public function releaseLapsed(): int
    {
        $released = 0;

        Registration::where('payment_status', 'unpaid')
            ->where('registration_status', 'pending')
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<', now()->subMinutes(5))
            ->each(function (Registration $registration) use (&$released) {
                $synced = $this->syncStatus($registration);

                if ($synced && $synced['became_paid']) {
                    $this->sendVerifiedMail($synced['registration']);
                }

                DB::transaction(function () use ($registration, &$released) {
                    $locked = Registration::lockForUpdate()->find($registration->id);

                    if ($locked && $locked->payment_status === 'unpaid' && $locked->registration_status === 'pending') {
                        $this->release($locked);
                        $locked->save();
                        $released++;
                    }
                });
            });

        return $released;
    }

    /**
     * New Snap page for an unpaid registration. A page that still works is returned as is; a lapsed
     * one is replaced and the seat taken again. A participant who already paid is credited first.
     *
     * @return array{url: ?string, paid: bool, error: ?string}
     */
    public function retryPayment(Registration $registration): array
    {
        if ($registration->payment_status !== 'unpaid') {
            return ['url' => null, 'paid' => in_array($registration->payment_status, ['paid', 'free'], true), 'error' => null];
        }

        if ($registration->registration_status === 'cancelled' && $registration->payment_expired_at === null) {
            return ['url' => null, 'paid' => false, 'error' => 'Pendaftaran ini dibatalkan panitia.'];
        }

        $synced = $this->syncStatus($registration);

        if ($synced) {
            if ($synced['became_paid']) {
                $this->sendVerifiedMail($synced['registration']);
            }

            if ($synced['registration']->payment_status === 'paid') {
                return ['url' => null, 'paid' => true, 'error' => null];
            }
        }

        $registration->refresh();

        if ($registration->registration_status === 'pending'
            && $registration->snap_redirect_url
            && $registration->payment_expires_at?->isFuture()) {
            return ['url' => $registration->snap_redirect_url, 'paid' => false, 'error' => null];
        }

        $reopened = DB::transaction(function () use ($registration) {
            $locked = Registration::with('category')->lockForUpdate()->find($registration->id);

            if ($locked->registration_status === 'cancelled') {
                $taken = Registration::where('category_id', $locked->category_id)
                    ->whereIn('registration_status', ['pending', 'confirmed'])
                    ->count();

                if ($locked->category?->quota && $taken >= $locked->category->quota) {
                    return false;
                }

                $locked->forceFill(['registration_status' => 'pending', 'payment_expired_at' => null])->save();
            }

            return true;
        });

        if (! $reopened) {
            return ['url' => null, 'paid' => false, 'error' => 'Kuota pendaftaran untuk kategori ini sudah penuh.'];
        }

        $url = $this->createPayment($registration->fresh(), retry: true);

        if (! $url) {
            // Put it back as it was: lapsed, so the participant can try again
            $registration->forceFill(['registration_status' => 'cancelled', 'payment_expired_at' => now()])->save();

            return ['url' => null, 'paid' => false, 'error' => 'Halaman pembayaran belum bisa dibuat. Coba lagi sebentar lagi.'];
        }

        return ['url' => $url, 'paid' => false, 'error' => null];
    }

    /**
     * Mail with the participant number once the payment is settled. A failed mail never fails the payment.
     */
    public function sendVerifiedMail(Registration $registration): void
    {
        try {
            Mail::to($registration->participant->email)->send(new RegistrationVerifiedMail($registration));
            $registration->participant->update(['email_sent_at' => now()]);
        } catch (Throwable $exception) {
            Log::error('Gagal kirim email verifikasi pembayaran: '.$exception->getMessage());
        }
    }

    /**
     * The order_id of the registration's current Snap attempt.
     */
    private function orderId(Registration $registration): string
    {
        return $registration->payment_order_id ?: $registration->registration_number;
    }

    /**
     * Retries carry a "-Rxxxxx" suffix on top of the registration number.
     */
    private function registrationNumber(string $orderId): string
    {
        return (string) preg_replace('/-R[a-z0-9]{5}$/', '', $orderId);
    }
}
