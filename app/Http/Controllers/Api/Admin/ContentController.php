<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;

class ContentController extends Controller
{
    public function index()
    {
        $contents = Content::orderBy('target_site')
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'message' => 'Daftar semua konten.',
            'data'    => ContentResource::collection($contents),
        ]);
    }

    public function store(StoreContentRequest $request)
    {
        $content = Content::create([
            'target_site' => $request->target_site,
            'section'     => $request->section,
            'title'       => $request->title,
            'body'        => $request->body,
            'status'      => $request->status,
            'sort_order'  => $request->sort_order ?? 0,
            'updated_by'  => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Konten berhasil dibuat.',
            'data'    => new ContentResource($content),
        ], 201);
    }

    public function show(string $id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Konten tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Detail konten.',
            'data'    => new ContentResource($content),
        ]);
    }

    public function update(UpdateContentRequest $request, string $id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Konten tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $content->update(array_merge(
            $request->validated(),
            ['updated_by' => $request->user()->id]
        ));

        return response()->json([
            'message' => 'Konten berhasil diperbarui.',
            'data'    => new ContentResource($content->fresh()),
        ]);
    }

    public function destroy(string $id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Konten tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $content->delete();

        return response()->json([
            'message' => 'Konten berhasil dihapus.',
            'data'    => null,
        ]);
    }
}