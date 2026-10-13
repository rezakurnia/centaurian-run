<?php

use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use App\Models\Package;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing', [
        'event' => Event::where('is_active', true)->first(),
        'categories' => Category::orderBy('id')->get(),
        'packages' => Package::orderBy('price')->get(),
        'contents' => Content::where('target_site', 'utama')
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section'),
    ]);
});

Route::get('/daftar', function () {
    return view('daftar', [
        'event' => Event::where('is_active', true)->first(),
        'categories' => Category::orderBy('id')->get(),
        'packages' => Package::orderBy('price')->get(),
    ]);
});

Route::view('/cek-status', 'cek-status');

Route::get('/hasil', fn () => view('hasil', ['categories' => Category::orderBy('id')->get()]));

// Console panitia/admin: halaman kosong, auth dan data lewat JS (token Sanctum di localStorage)
Route::view('/login', 'login');
Route::view('/panitia/scan', 'console.scan');
Route::view('/panitia/rekap', 'console.rekap');
