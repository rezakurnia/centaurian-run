<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Slim shape for the unauthenticated BIB check: no email, phone or other personal data.
 * The barcode is the scan credential, so it is only returned when the lookup included the
 * participant's email (registration numbers are sequential and easy to guess).
 */
class PublicRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'registration_number' => $this->registration_number,
            'full_name' => $this->participant->full_name ?? null,
            // Kept for the existing c-check-status.js, which reads r.participant?.full_name
            'participant' => ['full_name' => $this->participant->full_name ?? null],
            'category' => $this->category->name ?? null,
            'package' => $this->package->name ?? null,
            'event' => $this->event->name ?? null,
            'created_at' => $this->created_at,
            'registration_status' => $this->registration_status,
            'payment_status' => $this->payment_status,
            'bib_ready' => $this->isBibReady(),
            'payment_url' => $this->payment_status === 'unpaid' && $this->registration_status === 'pending' ? $this->snap_redirect_url : null,
            // Payment lapsed (or was abandoned): the participant can open a new payment page
            'can_retry_payment' => $this->payment_status === 'unpaid' && $this->registration_status === 'cancelled' && $this->payment_expired_at !== null,
            'barcode' => $this->when($request->filled('email') && $this->isBibReady(), $this->barcode),
        ];
    }

    private function isBibReady(): bool
    {
        return in_array($this->payment_status, ['paid', 'free'], true) && $this->registration_status !== 'cancelled';
    }
}
