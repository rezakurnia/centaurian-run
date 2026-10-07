<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'motivation' => $this->motivation,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_sent_at' => $this->email_sent_at,
            'created_at' => $this->created_at,
        ];
    }
}
