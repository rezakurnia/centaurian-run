<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Midtrans HTTP notification (webhook). The signature is checked before anything is
     * written, so only Midtrans can mark a registration as paid.
     */
    public function midtransNotification(Request $request, MidtransService $midtrans)
    {
        $payload = $request->all();

        if (! $midtrans->notificationIsValid($payload)) {
            Log::warning('Notifikasi Midtrans dengan signature tidak valid.', [
                'order_id' => $payload['order_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Signature notifikasi tidak valid.',
                'data' => null,
            ], 403);
        }

        $result = $midtrans->applyNotification($payload);

        if (! $result) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $registration = $result['registration'];

        if ($result['became_paid']) {
            $midtrans->sendVerifiedMail($registration);
        }

        return response()->json([
            'message' => 'Notifikasi pembayaran diproses.',
            'data' => [
                'registration_number' => $registration->registration_number,
                'payment_status' => $registration->payment_status,
                'registration_status' => $registration->registration_status,
            ],
        ]);
    }

    /**
     * Polled by /pembayaran/selesai: asks Midtrans for the latest transaction status so the
     * participant does not depend on the HTTP notification alone.
     */
    public function refreshStatus(string $registration_number, MidtransService $midtrans)
    {
        $registration = Registration::where('registration_number', $registration_number)->first();

        if (! $registration) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $synced = $midtrans->syncStatus($registration);

        if ($synced) {
            $registration = $synced['registration'];

            if ($synced['became_paid']) {
                $midtrans->sendVerifiedMail($registration);
            }
        }

        return response()->json([
            'message' => 'Status pembayaran.',
            'data' => [
                'registration_number' => $registration->registration_number,
                'payment_status' => $registration->payment_status,
                'registration_status' => $registration->registration_status,
                'paid_at' => $registration->paid_at,
            ],
        ]);
    }

    /**
     * "Bayar lagi": the participant's Snap page lapsed (24 hours) or was abandoned. Gives back a page
     * that works, taking the quota seat again; answers paid=true when the money already arrived.
     */
    public function retry(string $registration_number, MidtransService $midtrans)
    {
        $registration = Registration::where('registration_number', $registration_number)->first();

        if (! $registration) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $result = $midtrans->retryPayment($registration);

        if ($result['error']) {
            return response()->json([
                'message' => $result['error'],
                'data' => null,
            ], $result['url'] === null && str_contains($result['error'], 'Halaman') ? 503 : 422);
        }

        return response()->json([
            'message' => $result['paid'] ? 'Pembayaran sudah diterima.' : 'Halaman pembayaran siap.',
            'data' => ['paid' => $result['paid']],
            'payment' => $result['url'] ? ['redirect_url' => $result['url']] : null,
        ]);
    }
}
