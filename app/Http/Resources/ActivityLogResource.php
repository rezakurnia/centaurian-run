<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user'         => $this->user ? [
                'id'       => $this->user->id,
                'username' => $this->user->username,
                'role'     => $this->user->role,
            ] : null,
            'action'       => $this->action,
            'target_table' => $this->target_table,
            'target_id'    => $this->target_id,
            'detail'       => $this->detail,
            'ip_address'   => $this->ip_address,
            'user_agent'   => $this->user_agent,
            'created_at'   => $this->created_at,
        ];
    }
}