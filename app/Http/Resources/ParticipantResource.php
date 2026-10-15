<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'full_name'     => $this->full_name,
            'gender'        => $this->gender,
            'birth_place'   => $this->birth_place,
            'birth_date'    => $this->birth_date,
            'motivation'    => $this->motivation,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'email_sent_at' => $this->email_sent_at,
            'nip'             => $this->nip,
            'nis'             => $this->nis,
            'unit'            => $this->unit,
            'class'           => $this->class,
            'position'        => $this->position,
            'child_name'      => $this->child_name,
            'child_class'     => $this->child_class,
            'graduation_year' => $this->graduation_year,
            'created_at'    => $this->created_at,
        ];
    }
}