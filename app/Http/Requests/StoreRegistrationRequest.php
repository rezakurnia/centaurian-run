<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->input('category_id');
        $category = Category::find($categoryId);
        $code = $category?->code;

        $baseRules = [
            'full_name'   => 'required|string|max:100',
            'gender'      => 'required|in:L,P',
            'birth_place' => 'required|string|max:100',
            'birth_date'  => 'required|date',
            'motivation'  => 'nullable|string',
            'email'       => 'required|email|max:100',
            'phone'       => 'required|string|max:20',
            'event_id'    => 'required|exists:events,id',
            'category_id' => 'required|exists:categories,id',
           
        ];

        // Field spesifik per kategori
        $specificRules = match ($code) {
            'S' => [  // Siswa
                'nis'   => 'required|string|max:30',
                'class' => 'required|string|max:30',
            ],
            'E' => [  // Guru Karyawan
                'nip'      => 'required|string|max:30',
                'position' => 'required|string|max:50',
                'unit'     => 'required|string|max:50',
            ],
            'P' => [  // Orangtua
                'child_name'  => 'required|string|max:100',
                'child_class' => 'required|string|max:30',
            ],
            'A' => [  // Alumni
                'graduation_year' => 'required|integer|min:1900|max:' . date('Y'),
            ],
            default => [],
        };

        return array_merge($baseRules, $specificRules);
    }

    public function messages(): array
    {
        return [
            'nis.required'            => 'NIS wajib diisi untuk siswa.',
            'class.required'          => 'Kelas wajib diisi untuk siswa.',
            'nip.required'            => 'NIP wajib diisi untuk guru/karyawan.',
            'position.required'       => 'Jabatan wajib diisi untuk guru/karyawan.',
            'unit.required'           => 'Unit wajib diisi untuk guru/karyawan.',
            'child_name.required'     => 'Nama anak wajib diisi untuk orangtua.',
            'child_class.required'    => 'Kelas anak wajib diisi untuk orangtua.',
            'graduation_year.required' => 'Tahun lulus wajib diisi untuk alumni.',
        ];
    }
}