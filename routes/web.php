<?php

use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use App\Models\Package;
use App\Models\Registration;
use App\Services\SettingService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing', [
        'event' => Event::where('is_active', true)->first(),
        'categories' => Category::orderBy('id')->get(),
        'packages' => Package::where('is_active', true)->orderBy('price')->get(),
        'contents' => Content::where('target_site', 'utama')
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section'),
    ]);
});

Route::get('/daftar', function (SettingService $settings) {
    // Paket ditentukan backend dari kode kategori (RegistrationController::store); samakan di sini.
    $packages = Package::all()->keyBy('id');
    $packageFor = ['E' => 1, 'S' => 2, 'P' => 3, 'A' => 3];
    // Kuota 0 = tanpa batas; hitung pending + confirmed, sama seperti saat store.
    $taken = Registration::whereIn('registration_status', ['pending', 'confirmed'])
        ->selectRaw('category_id, count(*) as n')->groupBy('category_id')->pluck('n', 'category_id');
    $categories = Category::orderBy('id')->get()->each(function ($c) use ($packages, $packageFor, $taken) {
        $c->package = $packages[$packageFor[$c->code] ?? 1] ?? null;
        $c->remaining = $c->quota ? max(0, $c->quota - ($taken[$c->id] ?? 0)) : null;
    });
    $s = $settings->getAllFlat();
    $deadline = $s['registration_deadline'] ?? '';
    $closed = ($s['registration_open'] ?? '1') !== '1' || ($deadline && now()->toDateString() > $deadline);

    return view('daftar', [
        'event' => Event::where('is_active', true)->first(),
        'categories' => $categories,
        'closed' => $closed,
    ]);
});

Route::view('/cek-status', 'cek-status');

Route::get('/hasil', fn () => view('hasil', ['categories' => Category::orderBy('id')->get()]));

// Console panitia/admin: halaman kosong, auth dan data lewat JS (token Sanctum di localStorage)
Route::view('/login', 'login');
Route::view('/panitia/scan', 'console.scan');
Route::view('/panitia/rekap', 'console.rekap');
Route::view('/admin', 'console.dashboard', ['role' => 'admin']);
Route::view('/admin/pendaftaran', 'console.pendaftaran', ['role' => 'admin']);
Route::view('/admin/peserta', 'console.peserta', ['role' => 'admin']);
foreach (['event' => ['Event', 'events'], 'konten' => ['Konten', 'contents'], 'pengguna' => ['Pengguna', 'users']] as $slug => [$title, $resource]) {
    Route::view("/admin/$slug", 'console.crud', ['role' => 'admin', 'title' => $title, 'resource' => $resource]);
}
Route::view('/admin/pengaturan', 'console.pengaturan', ['role' => 'admin']);
Route::view('/admin/scan-log', 'console.logs', ['role' => 'admin', 'title' => 'Log scan', 'log' => 'scan']);
Route::view('/admin/log-aktivitas', 'console.logs', ['role' => 'admin', 'title' => 'Log aktivitas', 'log' => 'activity']);
Route::view('/admin/ekspor', 'console.ekspor', ['role' => 'admin']);

Route::get('/hasil/{registration_number}', fn (string $registration_number) => view('hasil-detail', ['no' => $registration_number]));
