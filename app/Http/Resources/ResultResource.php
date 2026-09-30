<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'registration_number' => $this->registration->registration_number ?? null,
            'full_name'           => $this->participant->full_name ?? null,
            'category'            => $this->registration->category->name ?? null,
            'start_time'          => $this->start_time,
            'finish_time'         => $this->finish_time,
            'duration'            => $this->duration,
            'scan_status'         => $this->scan_status,
        ];
    }
}