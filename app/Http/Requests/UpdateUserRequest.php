<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'username' => 'sometimes|string|max:50|unique:users,username,' . $userId,
            'password' => 'nullable|string|min:6',
            'email'    => 'nullable|email|max:100',
            'role'     => 'sometimes|in:admin,panitia',
        ];
    }
}