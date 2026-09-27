<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_site' => 'required|in:utama,panitia',
            'section'     => 'nullable|string|max:50',
            'title'       => 'nullable|string|max:150',
            'body'        => 'nullable|string',
            'status'      => 'required|in:draft,published',
            'sort_order'  => 'nullable|integer',
        ];
    }
}