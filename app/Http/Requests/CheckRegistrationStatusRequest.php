<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckRegistrationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_number' => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:100',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('registration_number') && !$this->filled('email')) {
                $validator->errors()->add(
                    'identifier',
                    'Isi minimal salah satu: registration_number atau email.'
                );
            }
        });
    }
}