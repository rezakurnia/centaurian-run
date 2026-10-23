<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Registration;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('id')->get();

        return response()->json([
            'message' => 'Daftar kategori peserta.',
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Kuota kategori — info sisa kuota.
     */
    public function quota()
    {
        $categories = Category::orderBy('id')->get();

        $result = $categories->map(function ($category) {
            $registeredCount = Registration::where('category_id', $category->id)
                ->whereIn('registration_status', ['pending', 'confirmed'])
                ->count();
            $remaining = max(0, $category->quota - $registeredCount);

            return [
                'id'              => $category->id,
                'name'            => $category->name,
                'code'            => $category->code,
                'quota'           => $category->quota,
                'registered'      => $registeredCount,
                'remaining'       => $remaining,
                'is_full'         => $remaining <= 0,
            ];
        });

        return response()->json([
            'message' => 'Info kuota kategori.',
            'data'    => $result,
        ]);
    }
}
