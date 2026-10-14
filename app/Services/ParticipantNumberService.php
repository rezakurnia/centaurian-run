<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class ParticipantNumberService
{
    /**
     * Generate nomor peserta untuk registrasi via WEB.
     *
     * Aturan:
     * - Kode kategori dari `categories.code` (S, E, P, A)
     * - Range GLOBAL per kode — bukan per kategori
     * - Siswa SMP & SMA share kode 'S' → nomor dinamis
     * - Cek kuota kategori sebelum generate
     */
    public function generate(int $categoryId): array
    {
        return DB::transaction(function () use ($categoryId) {
            $category = Category::findOrFail($categoryId);

            // Cek kuota kategori
            $registeredCount = Registration::where('category_id', $categoryId)->count();

            if ($category->quota > 0 && $registeredCount >= $category->quota) {
                throw new \Exception("Kuota kategori {$category->name} sudah penuh (max: {$category->quota}).");
            }

            // Ambil nomor terakhir dengan kode SAMA (global per kode)
            $lastSequence = DB::table('registrations')
                ->join('categories', 'categories.id', '=', 'registrations.category_id')
                ->where('categories.code', $category->code)
                ->lockForUpdate()
                ->max('registrations.sequence_number') ?? 0;

            $nextSequence = $lastSequence + 1;
            $number = $category->code . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            return [
                'registration_number' => $number,
                'sequence_number'     => $nextSequence,
            ];
        });
    }

    /**
     * Generate nomor peserta untuk IMPORT.
     *
     * Aturan: sama dengan WEB, tapi bisa dipakai untuk import gratis.
     */
    public function generateForImport(int $categoryId): array
    {
        return $this->generate($categoryId);
    }
}