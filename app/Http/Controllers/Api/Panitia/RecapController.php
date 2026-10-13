<?php

namespace App\Http\Controllers\Api\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RecapController extends Controller
{
    public function byCategory()
    {
        $recap = Category::leftJoin('registrations', 'registrations.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                'categories.code',
                DB::raw('COUNT(registrations.id) AS total_participants')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.code')
            ->orderBy('categories.id')
            ->get();

        return response()->json([
            'message' => 'Rekap jumlah peserta per kategori.',
            'data' => $recap,
        ]);
    }

    public function byRegistrationNumber()
    {
        $recap = Registration::with(['participant', 'category', 'package'])
            ->orderBy('registration_number', 'asc')
            ->get()
            ->map(function ($reg) {
                return [
                    'registration_number' => $reg->registration_number,
                    'full_name' => $reg->participant->full_name ?? null,
                    'category' => $reg->category->name ?? null,
                    'package' => $reg->package->name ?? null,
                    'registration_status' => $reg->registration_status,
                    'payment_status' => $reg->payment_status,
                ];
            });

        return response()->json([
            'message' => 'Rekap peserta berdasarkan nomor peserta.',
            'data' => $recap,
        ]);
    }
}
