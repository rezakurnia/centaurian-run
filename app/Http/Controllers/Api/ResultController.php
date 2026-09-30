<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;
use App\Models\Result;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = Result::with(['participant', 'registration.category'])
            ->where('scan_status', 'valid');

        if ($request->filled('category_id')) {
            $query->whereHas('registration', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $results = $query->orderBy('duration', 'asc')->get();

        return response()->json([
            'message' => 'Daftar waktu tempuh peserta.',
            'data'    => ResultResource::collection($results),
        ]);
    }

    public function show(string $registrationNumber)
    {
        $result = Result::with(['participant', 'registration.category'])
            ->whereHas('registration', function ($q) use ($registrationNumber) {
                $q->where('registration_number', $registrationNumber);
            })
            ->where('scan_status', 'valid')
            ->first();

        if (!$result) {
            return response()->json([
                'message' => 'Hasil tidak ditemukan untuk nomor peserta tersebut.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Hasil waktu tempuh peserta.',
            'data'    => new ResultResource($result),
        ]);
    }
}