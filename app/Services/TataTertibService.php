<?php

namespace App\Services;

class TataTertibService
{
    /**
     * Ubah teks tata tertib dari Admin → Pengaturan menjadi daftar bagian + butir.
     *
     * Format teks (lihat default `tata_tertib` di config/app_settings.php):
     * - Baris diawali "#"  → judul bagian baru
     * - Baris lain         → satu butir, ditulis "Label: isi"; tanpa ":" jadi butir tanpa label
     * - Baris kosong       → diabaikan
     * Baris sebelum judul bagian pertama diabaikan, supaya teks nyasar tidak merusak tampilan.
     *
     * @return array<int, array{title: string, items: array<int, array{label: string, text: string}>}>
     */
    public function groups(string $text): array
    {
        $groups = [];

        foreach (preg_split('/\R/', $text) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '#')) {
                $groups[] = ['title' => trim(ltrim($line, '#')), 'items' => []];

                continue;
            }

            if ($groups === []) {
                continue;
            }

            $groups[array_key_last($groups)]['items'][] = $this->item($line);
        }

        return $groups;
    }

    /**
     * Pecah satu baris butir menjadi label + isi.
     *
     * @return array{label: string, text: string}
     */
    private function item(string $line): array
    {
        if (! str_contains($line, ':')) {
            return ['label' => '', 'text' => $line];
        }

        [$label, $text] = explode(':', $line, 2);

        // "Label:" tanpa isi diperlakukan sebagai teks biasa, bukan label menggantung
        if (trim($text) === '') {
            return ['label' => '', 'text' => $line];
        }

        return ['label' => trim($label), 'text' => trim($text)];
    }
}
