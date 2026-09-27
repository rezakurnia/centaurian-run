<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_status' => 'required|in:pending,confirmed,cancelled',
            'payment_status'      => 'required|in:unpaid,paid,free',
        ];
    }
}