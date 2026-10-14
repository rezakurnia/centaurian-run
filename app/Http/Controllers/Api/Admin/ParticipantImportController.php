<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ParticipantsImport;
use App\Services\ParticipantImportService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantImportController extends Controller
{
    use LogsActivity;

    public function import(Request $request, ParticipantImportService $importService)
    {
        $request->validate([
            'file'       => 'required|file|mimes:xlsx,xls|max:5120',
            'type'       => 'required|in:siswa,guru',
            'event_id'   => 'required|exists:events,id',
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            // Baca file Excel dengan class Import
            $import = new ParticipantsImport();
            Excel::import($import, $request->file('file'));

            $rows = $import->data;

            if (empty($rows)) {
                return response()->json([
                    'message' => 'File Excel kosong atau tidak ada data.',
                    'data'    => null,
                ], 422);
            }

            // Normalize key (lowercase, underscore)
            $normalizedRows = array_map(function ($row) {
                $newRow = [];
                foreach ($row as $key => $value) {
                    // Normalisasi: "Nama Lengkap" → "nama_lengkap"
                    $cleanKey = strtolower(trim($key));
                    $cleanKey = preg_replace('/[^a-z0-9]+/', '_', $cleanKey);
                    $cleanKey = trim($cleanKey, '_');
                    $newRow[$cleanKey] = $value;
                }
                return $newRow;
            }, $rows);

            // Proses import
            $result = $importService->import(
                $normalizedRows,
                $request->type,
                $request->event_id,
                $request->package_id,
                $request->user()->id
            );

            $this->logActivity(
                'import_participants',
                'participants',
                null,
                "Import {$request->type}: {$result['success_count']} sukses, {$result['skipped_count']} skip, {$result['error_count']} error"
            );

            return response()->json([
                'message' => "Import selesai: {$result['success_count']} berhasil, {$result['skipped_count']} di-skip, {$result['error_count']} error.",
                'data'    => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
                'data'    => null,
            ], 422);
        }
    }
}