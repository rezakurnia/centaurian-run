<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barcode' => $this->barcode,
            'scan_status' => $this->scan_status,
            'participant' => $this->participant ? [
                'id' => $this->participant->id,
                'full_name' => $this->participant->full_name,
                'email' => $this->participant->email,
            ] : null,
            'scanned_by' => $this->scannedBy ? [
                'id' => $this->scannedBy->id,
                'username' => $this->scannedBy->username,
                'role' => $this->scannedBy->role,
            ] : null,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'scanned_at' => $this->scanned_at,
            'created_at' => $this->created_at,
        ];
    }
}
