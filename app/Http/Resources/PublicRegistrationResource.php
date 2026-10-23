<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Slim shape for the unauthenticated BIB check: no email, phone, barcode, or other personal data.
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
            'bib_ready' => in_array($this->payment_status, ['paid', 'free'], true) && $this->registration_status !== 'cancelled',
            'payment_url' => $this->payment_status === 'unpaid' ? $this->snap_redirect_url : null,
        ];
    }
}
