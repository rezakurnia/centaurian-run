<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

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
}
