@extends('layouts.console')
@section('title', 'Scan finish')
@section('content')
<section class="k-card" id="start-card">
    <h2 class="k-h2">Mulai dan akhiri acara</h2>
    <p id="start-info" class="body-md">Memuat acara aktif…</p>
    <p class="c-error mt-3" id="start-error" role="alert" hidden></p>
    <div class="k-row mt-4">
        <button type="button" id="start-btn" class="k-btn" disabled>Mulai serentak</button>
        <button type="button" id="finish-btn" class="k-btn -ghost" hidden>Akhiri acara</button>
    </div>
</section>

<section class="k-card" id="scan-card">
    <h2 class="k-h2">Scan barcode peserta</h2>
    <form id="scan-form" class="k-row" novalidate>
        <label class="sr-only" for="barcode">Barcode</label>
        <input class="c-input" id="barcode" name="barcode" type="text" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="mis. A1B2C3D4-SW-0001">
        <button type="submit" class="k-btn" id="scan-btn">Scan</button>
        <button type="button" class="k-btn -ghost" id="cam-btn" hidden>Kamera</button>
    </form>
    <video id="cam" class="k-cam" playsinline muted hidden></video>
    <div id="scan-result" class="k-result" aria-live="polite"></div>
</section>

<section class="k-card">
    <h2 class="k-h2">Riwayat sesi ini</h2>
    <div class="k-scroll" tabindex="0" role="region" aria-label="Tabel data">
        <table class="c-table">
            <thead><tr><th>Jam</th><th>Peserta</th><th>Kategori</th><th>Waktu</th><th>Status</th></tr></thead>
            <tbody id="scan-history"><tr><td colspan="5">Belum ada scan.</td></tr></tbody>
        </table>
    </div>
</section>
@endsection
