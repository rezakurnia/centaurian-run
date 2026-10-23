<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentResource;
use App\Models\Content;

class ContentController extends Controller
{
    public function index()
    {
        $contents = Content::where('target_site', 'utama')
            ->where('status', 'published')
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'message' => 'Daftar konten website utama.',
            'data' => ContentResource::collection($contents),
        ]);
    }

    /**
     * Pengumuman untuk panitia (target_site "panitia"); rute ini dilindungi role panitia/admin.
     */
    public function announcements()
    {
        $contents = Content::where('target_site', 'panitia')
            ->where('status', 'published')
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'message' => 'Pengumuman panitia.',
            'data' => ContentResource::collection($contents),
        ]);
    }
}
