@extends('layouts.console')
@section('title', 'Rekap peserta')
@section('content')
<section class="k-card">
    <h2 class="k-h2">Per kategori</h2>
    <div class="k-scroll" tabindex="0" role="region" aria-label="Tabel data">
        <table class="c-table">
            <thead><tr><th>Kategori</th><th>Kode</th><th>Peserta</th></tr></thead>
            <tbody id="recap-category"><tr><td colspan="3">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>

<section class="k-card">
    <h2 class="k-h2">Per nomor peserta</h2>
    <label class="c-label" for="recap-q">Cari nomor atau nama</label>
    <input class="c-input mt-2 mb-4" id="recap-q" type="search" autocomplete="off">
    <div class="k-scroll" tabindex="0" role="region" aria-label="Tabel data">
        <table class="c-table">
            <thead><tr><th>No.</th><th>Nama</th><th>Kategori</th><th>Paket</th><th>Pendaftaran</th><th>Bayar</th></tr></thead>
            <tbody id="recap-participants"><tr><td colspan="6">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>
@endsection
