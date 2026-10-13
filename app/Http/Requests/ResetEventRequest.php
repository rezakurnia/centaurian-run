<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ResetEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirmation' => 'required|string|in:RESET',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation.required' => 'Konfirmasi wajib diisi.',
            'confirmation.in' => 'Ketik "RESET" (huruf kapital) untuk konfirmasi.',
            'password.required' => 'Password admin wajib diisi.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('password')) {
                $user = $this->user();
                if (! $user || ! Hash::check($this->password, $user->password)) {
                    $validator->errors()->add(
                        'password',
                        'Password admin salah.'
                    );
                }
            }
        });
    }
}
