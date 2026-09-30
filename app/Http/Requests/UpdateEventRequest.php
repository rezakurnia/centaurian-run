<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:150',
            'event_date'  => 'sometimes|date',
            'start_time'  => 'nullable|date',
            'is_active'   => 'boolean',
        ];
    }
}