<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('price')->get();

        return response()->json([
            'message' => 'Daftar paket pendaftaran.',
            'data' => PackageResource::collection($packages),
        ]);
    }
}
