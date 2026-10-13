<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function participants(Request $request): StreamedResponse
    {
        $query = Participant::query();

        if ($request->filled('category_id')) {
            $query->whereHas('registrations', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $participants = $query->orderBy('id')->cursor();

        $filename = 'participants_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($participants) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'ID', 'Nama', 'Gender', 'Tempat Lahir', 'Tanggal Lahir',
                'Email', 'Phone', 'Motivasi', 'Email Terkirim', 'Terdaftar Pada',
            ]);

            // Data
            foreach ($participants as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->full_name,
                    $p->gender,
                    $p->birth_place,
                    $p->birth_date,
                    $p->email,
                    $p->phone,
                    $p->motivation,
                    $p->email_sent_at,
                    $p->created_at,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function registrations(Request $request): StreamedResponse
    {
        $query = Registration::with(['participant', 'category', 'package', 'event']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('registration_status')) {
            $query->where('registration_status', $request->registration_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $registrations = $query->orderBy('created_at', 'desc')->cursor();

        $filename = 'registrations_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'No Peserta', 'Barcode', 'Nama', 'Email', 'Phone',
                'Kategori', 'Paket', 'Event',
                'Status Pendaftaran', 'Status Pembayaran', 'Terdaftar Pada',
            ]);

            // Data
            foreach ($registrations as $r) {
                fputcsv($handle, [
                    $r->registration_number,
                    $r->barcode,
                    $r->participant->full_name ?? '-',
                    $r->participant->email ?? '-',
                    $r->participant->phone ?? '-',
                    $r->category->name ?? '-',
                    $r->package->name ?? '-',
                    $r->event->name ?? '-',
                    $r->registration_status,
                    $r->payment_status,
                    $r->created_at,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
