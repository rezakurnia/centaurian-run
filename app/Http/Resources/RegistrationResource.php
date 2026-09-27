<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'registration_number' => $this->registration_number,
            'barcode'             => $this->barcode,
            'registration_status' => $this->registration_status,
            'payment_status'      => $this->payment_status,
            'category'            => $this->category->name ?? null,
            'package'             => $this->package->name ?? null,
            'event'               => $this->event->name ?? null,
            'created_at'          => $this->created_at,
        ];
    }
}