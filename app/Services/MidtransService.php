<?php

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     * Midtrans is not configured, or the API call fails — the registration itself is
     * already stored, so the manual payment-proof flow stays available as a fallback.
     */
    public function createPayment(Registration $registration): ?string
    {
        $price = (int) ($registration->package?->price ?? 0);

        if ($price <= 0 || ! $this->isConfigured()) {
            return null;
        }

        $serverKey = (string) config('services.midtrans.server_key');

        $payload = [
            'transaction_details' => [
                'order_id' => $registration->registration_number,
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
                ->get($this->statusEndpoint($registration->registration_number));

            $payload = $response->json();

            // Before the participant starts paying, the Core API answers 404 "Transaction not found"
            if (! is_array($payload) || ($payload['order_id'] ?? '') !== $registration->registration_number) {
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
     * Applies a verified notification. Safe to run more than once: a registration that is
     * already paid is left untouched, and pending/deny/cancel/expire keep it unpaid.
     *
     * @return array{registration: Registration, became_paid: bool}|null
     */
    public function applyNotification(array $payload): ?array
    {
        $registration = Registration::with(['participant', 'category', 'package', 'event'])
            ->where('registration_number', (string) ($payload['order_id'] ?? ''))
            ->first();

        if (! $registration) {
            return null;
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');
        $settled = in_array($transactionStatus, ['capture', 'settlement'], true)
            && ! in_array($fraudStatus, ['deny', 'challenge'], true);

        $registration->payment_method = $payload['payment_type'] ?? $registration->payment_method;
        $becamePaid = $settled && $registration->payment_status !== 'paid';

        if ($becamePaid) {
            $registration->payment_status = 'paid';
            $registration->registration_status = 'confirmed';
            $registration->paid_at = now();
            $registration->verified_at = now();
        }

        $registration->save();

        return ['registration' => $registration->fresh(['participant', 'category', 'package', 'event']), 'became_paid' => $becamePaid];
    }
}
