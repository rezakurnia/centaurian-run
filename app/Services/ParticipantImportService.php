<?php

namespace App\Services;

use App\Models\Category;
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

                $seenEmails[] = $email;

                $gender = strtoupper($this->getValue($row, ['jenis_kelamin_l_p', 'jenis_kelamin', 'gender', 'gender_l_p']));

                DB::transaction(function () use ($row, $category, $eventId, $packageId, $type, $email, $fullName, $gender) {
                    // Generate nomor
                    $generated = $this->numberService->generateForImport($category->id);

                    // Buat participant
                    $participant = Participant::create([
                    'full_name'       => $row['full_name'] ?? '',
                    'gender'          => strtoupper($row['gender'] ?? '') === 'L' ? 'L' : 'P',
                    'birth_place'     => $row['birth_place'] ?? null,
                    'birth_date'      => $row['birth_date'] ?? null,
                    'motivation'      => $row['motivation'] ?? null,
                    'email'           => $row['email'],
                    'phone'           => $row['phone'] ?? null,
                    'nip'             => $row['nip'] ?? null,
                    'nis'             => $row['nis'] ?? null,
                    'unit'            => $row['unit'] ?? null,
                    'class'           => $row['class'] ?? null,
                    'position'        => $row['position'] ?? null,
                    'child_name'      => $row['child_name'] ?? null,
                    'child_class'     => $row['child_class'] ?? null,
                    'graduation_year' => $row['graduation_year'] ?? null,
                ]);

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
}