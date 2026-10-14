<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Website Utama (Publik)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('utama.index');
})->name('home');

Route::get('/registrasi', function () {
    return view('registrasi.index');
})->name('registrasi');

Route::get('/registrasi/form', function () {
    return view('registrasi.form');
})->name('registrasi.form');

Route::get('/registrasi/sukses', function () {
    return view('registrasi.sukses');
})->name('registrasi.sukses');

Route::get('/hasil', function () {
    return view('hasil.index');
})->name('hasil');

/*
|--------------------------------------------------------------------------
| Website Panitia
|--------------------------------------------------------------------------
*/

Route::prefix('panitia')->name('panitia.')->group(function () {
    Route::get('/', function () {
        return view('panitia.dashboard');
    })->name('dashboard');

    Route::get('/scan', function () {
        return view('panitia.scan');
    })->name('scan');
});

/*
|--------------------------------------------------------------------------
| Website Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Data Peserta
    Route::get('/peserta', function () {
        return view('admin.peserta');
    })->name('peserta');

    // Event
    Route::get('/event', function () {
        return view('admin.event');
    })->name('event');

    // Konten
    Route::get('/konten', function () {
        return view('admin.konten');
    })->name('konten');

    // User
    Route::get('/user', function () {
        return view('admin.user');
    })->name('user');

    // Scan Logs
    Route::get('/scan-logs', function () {
        return view('admin.scan-logs');
    })->name('scan-logs');

    // Activity Logs
    Route::get('/activity', function () {
    return view('admin.activity');
    })->name('activity');

    // Settings
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

    // Export
    Route::get('/export', function () {
        return view('admin.export');
    })->name('export');
});