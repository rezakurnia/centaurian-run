<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantTemplateController extends Controller
{
    public function siswa(): StreamedResponse
    {
        return $this->generate([
            'NIS', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'No HP', 'Email', 'Kategori (Siswa)', 'Kelas',
        ], 'template_import_siswa.xlsx', 'Siswa');
    }

    public function guru(): StreamedResponse
    {
        return $this->generate([
            'NIP', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'No HP', 'Email', 'Kategori (Guru/Karyawan)', 'Unit',
        ], 'template_import_guru.xlsx', 'Guru');
    }

    private function generate(array $headers, string $filename, string $sheetName): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        // Header
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Baris contoh
        if ($sheetName === 'Siswa') {
            $sheet->fromArray(['12345', 'Budi Santoso', 'L', '08123456789', 'budi@example.com', 'Siswa', '5A'], null, 'A2');
        } else {
            $sheet->fromArray(['NIP001', 'Ibu Sari', 'P', '08123456780', 'sari@example.com', 'Guru', 'SD'], null, 'A2');
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}