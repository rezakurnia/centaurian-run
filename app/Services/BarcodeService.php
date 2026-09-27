<?php

namespace App\Services;

use Illuminate\Support\Str;

class BarcodeService
{
    public function generate(string $registrationNumber): string
    {
        return strtoupper(Str::random(8)) . '-' . $registrationNumber;
    }
}