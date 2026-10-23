<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class ParticipantImportService
{
    public function __construct(
        protected ParticipantNumberService $numberService,
        protected BarcodeService $barcodeService
    ) {
    }

    /**
     * Proses import dari array data Excel.
     */
    public function import(array $rows, string $type, int $eventId, int $packageId, int $userId): array
    {
        $success = [];
        $errors = [];
        $skipped = [];

        // Tentukan kategori
        $categoryCode = $type === 'siswa' ? 'S' : 'E';
        $category = Category::where('code', $categoryCode)->firstOrFail();

        $seenEmails = [];
        $seenNips = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Baris Excel (2 = setelah header)

            try {
                // Ambil email & nama — support beberapa format header
                $email = $this->getValue($row, ['email']);
                $fullName = $this->getValue($row, ['nama_lengkap', 'nama', 'name', 'full_name']);

                if (empty($email)) {
                    $errors[] = "Baris {$rowNum}: Email kosong.";
                    continue;
                }

                if (empty($fullName)) {
                    $errors[] = "Baris {$rowNum}: Nama lengkap kosong.";
                    continue;
                }

                // Cek duplikat email di file
                if (in_array($email, $seenEmails)) {
                    $skipped[] = "Baris {$rowNum}: Email '{$email}' duplikat dalam file.";
                    continue;
                }

                // Cek duplikat email di database
                if (Participant::where('email', $email)->exists()) {
                    $skipped[] = "Baris {$rowNum}: Email '{$email}' sudah terdaftar.";
                    continue;
                }

                // Guru/karyawan: NIP must be in the employee master, one registration per NIP per event,
                // and the category quota (pending + confirmed, same as the public form) must have room.
                if ($type === 'guru') {
                    $nip = $this->getValue($row, ['nip']);

                    if (empty($nip) || ! Employee::where('nip', $nip)->exists()) {
                        $errors[] = "Baris {$rowNum}: NIP '{$nip}' tidak terdaftar sebagai guru/karyawan.";
                        continue;
                    }

                    if (in_array($nip, $seenNips, true) || Registration::where('event_id', $eventId)
                        ->whereHas('participant', fn ($q) => $q->where('nip', $nip))->exists()) {
                        $skipped[] = "Baris {$rowNum}: NIP '{$nip}' sudah terdaftar pada event ini.";
                        continue;
                    }

                    $filled = Registration::where('category_id', $category->id)
                        ->whereIn('registration_status', ['pending', 'confirmed'])->count();
                    if ($category->quota && $filled >= $category->quota) {
                        $errors[] = "Baris {$rowNum}: Kuota kategori sudah penuh.";
                        continue;
                    }

                    $seenNips[] = $nip;
                }

                $seenEmails[] = $email;

                $gender = $this->normalizeGender(
                    $this->getValue($row, ['jenis_kelamin_l_p', 'jenis_kelamin', 'gender', 'gender_l_p'])
                );

                // Petakan heading template Excel → kolom tabel `participants`.
                // Heading sudah dinormalisasi controller menjadi snake_case
                // (mis. "Nama Lengkap" → "nama_lengkap", "No HP" → "no_hp"),
                // karena itu tiap field dibaca lewat beberapa alias.
                $attributes = [
                    'full_name'       => $fullName,
                    'gender'          => $gender,
                    'birth_place'     => $this->getValue($row, ['birth_place', 'tempat_lahir']),
                    'birth_date'      => $this->parseDate($this->getValue($row, ['birth_date', 'tanggal_lahir'])),
                    'motivation'      => $this->getValue($row, ['motivation', 'motivasi']),
                    'email'           => $email,
                    'phone'           => $this->getValue($row, ['no_hp', 'phone', 'no_telepon', 'nomor_hp', 'hp']),
                    'nip'             => $this->getValue($row, ['nip']),
                    'nis'             => $this->getValue($row, ['nis']),
                    'unit'            => $this->getValue($row, ['unit']),
                    'class'           => $this->getValue($row, ['kelas', 'class']),
                    'position'        => $this->getValue($row, ['position', 'jabatan']),
                    'child_name'      => $this->getValue($row, ['child_name', 'nama_anak']),
                    'child_class'     => $this->getValue($row, ['child_class', 'kelas_anak']),
                    'graduation_year' => $this->getValue($row, ['graduation_year', 'tahun_lulus']),
                ];

                DB::transaction(function () use ($attributes, $category, $eventId, $packageId) {
                    // Generate nomor
                    $generated = $this->numberService->generateForImport($category->id);

                    // Buat participant
                    $participant = Participant::create($attributes);

                    // Buat registration
                    Registration::create([
                        'registration_number' => $generated['registration_number'],
                        'sequence_number'     => $generated['sequence_number'],
                        'barcode'             => $this->barcodeService->generate($generated['registration_number']),
                        'participant_id'      => $participant->id,
                        'event_id'            => $eventId,
                        'category_id'         => $category->id,
                        'package_id'          => $packageId,
                        'registration_status' => 'confirmed',
                        'payment_status'      => 'free',
                    ]);
                });

                $success[] = "Baris {$rowNum}: {$fullName} ({$email})";

            } catch (\Exception $e) {
                $errors[] = "Baris {$rowNum}: " . $e->getMessage();
            }
        }

        return [
            'success'       => $success,
            'skipped'       => $skipped,
            'errors'        => $errors,
            'total'         => count($rows),
            'success_count' => count($success),
            'skipped_count' => count($skipped),
            'error_count'   => count($errors),
        ];
    }

    /**
     * Ambil nilai dari row dengan berbagai kemungkinan key.
     */
    private function getValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && !empty(trim((string) $row[$key]))) {
                return trim((string) $row[$key]);
            }
        }
        return null;
    }

    /**
     * Parse tanggal dari Excel.
     */
    private function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;

        try {
            // Format Excel serial date?
            if (is_numeric($value)) {
                return \Carbon\Carbon::createFromTimestamp(($value - 25569) * 86400)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Normalisasi jenis kelamin dari kolom Excel.
     *
     * Kosong → null (tidak menebak), selain itu dipetakan ke 'L' atau 'P'.
     */
    private function normalizeGender(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        if ($value === '') {
            return null;
        }

        return in_array($value, ['L', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA', 'MALE'], true) ? 'L' : 'P';
    }
}