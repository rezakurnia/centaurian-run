<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'   => 'required|string|max:100',
            'gender'      => 'required|in:L,P',
            'birth_place' => 'required|string|max:100',
            'birth_date'  => 'required|date',
            'motivation'  => 'nullable|string',
            'email'       => 'required|email|max:100',
            'phone'       => 'required|string|max:20',
            'event_id'    => 'required|exists:events,id',
            'category_id' => 'required|exists:categories,id',
            'package_id'  => 'required|exists:packages,id',
        ];
    }
}