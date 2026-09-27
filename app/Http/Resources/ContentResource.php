<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'section'    => $this->section,
            'title'      => $this->title,
            'body'       => $this->body,
            'sort_order' => $this->sort_order,
            'updated_at' => $this->updated_at,
        ];
    }
}