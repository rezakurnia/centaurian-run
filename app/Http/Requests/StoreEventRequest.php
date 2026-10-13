<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:150',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date',
            'is_active' => 'boolean',
        ];
    }
}
