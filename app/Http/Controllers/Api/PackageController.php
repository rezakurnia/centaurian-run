<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;

class PackageController extends Controller
{
    /**
     * Daftar paket AKTIF (untuk halaman publik).
     */
    public function index()
    {
        $packages = Package::where('is_active', true)
            ->orderBy('price')
            ->get();

        return response()->json([
            'message' => 'Daftar paket pendaftaran.',
            'data' => PackageResource::collection($packages),
        ]);
    }

    /**
     * Daftar SEMUA paket (untuk admin).
     */
    public function adminIndex()
    {
        $packages = Package::orderBy('id')->get();

        return response()->json([
            'message' => 'Daftar semua paket (termasuk non-aktif).',
            'data'    => PackageResource::collection($packages),
        ]);
    }
}
