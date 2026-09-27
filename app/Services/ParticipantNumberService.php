<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class ParticipantNumberService
{
    public function generate(int $categoryId): array
    {
        return DB::transaction(function () use ($categoryId) {
            $category = Category::findOrFail($categoryId);

            $lastSequence = Registration::where('category_id', $categoryId)
                ->lockForUpdate()
                ->max('sequence_number') ?? 0;

            $nextSequence = $lastSequence + 1;
            $number = $category->code . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            return [
                'registration_number' => $number,
                'sequence_number'     => $nextSequence,
            ];
        });
    }
}