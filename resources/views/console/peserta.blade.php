@extends('layouts.console')
@section('title', 'Peserta')
@section('content')
<section class="k-card">
    <form id="p-filter" class="k-row" aria-label="Filter peserta">
        <input class="c-input" name="search" type="search" placeholder="Cari nama, email, atau nomor" aria-label="Cari peserta" autocomplete="off">
        <select class="c-input" name="category_id" aria-label="Kategori"><option value="">Semua kategori</option></select>
    </form>
    <div class="k-scroll mt-4">
        <table class="c-table">
            <thead><tr><th>Nama</th><th>Identitas</th><th>L/P</th><th>Email</th><th>Telepon</th><th>Daftar</th></tr></thead>
            <tbody id="p-body"><tr><td colspan="6">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>

{{-- Bulk import from the xlsx templates; handled by import.js --}}
<section class="k-card" aria-labelledby="imp-title">
    <h2 class="k-h2" id="imp-title">Impor peserta</h2>
    <p class="body-md">Unduh templatnya, isi satu peserta per baris, lalu unggah. Peserta impor langsung terkonfirmasi untuk acara aktif.</p>
    <div class="k-row mt-4">
        <button type="button" class="k-btn -ghost" data-template="siswa">Templat siswa</button>
        <button type="button" class="k-btn -ghost" data-template="guru">Templat guru</button>
    </div>
    <form id="imp-form" class="mt-5 grid gap-4 sm:grid-cols-2" novalidate>
        <div class="c-field">
            <label class="c-label" for="imp-type">Jenis</label>
            <select class="c-input" id="imp-type" name="type"><option value="siswa">Siswa</option><option value="guru">Guru / karyawan</option></select>
        </div>
        <div class="c-field">
            <label class="c-label" for="imp-package">Paket</label>
            <select class="c-input" id="imp-package" name="package_id"><option value="">Memuat…</option></select>
        </div>
        <div class="c-field sm:col-span-2">
            <label class="c-label" for="imp-file">File Excel (.xlsx, maks. 5 MB)</label>
            <input class="c-input c-file" id="imp-file" name="file" type="file" accept=".xlsx,.xls">
        </div>
        <p class="c-error sm:col-span-2" id="imp-error" role="alert" hidden></p>
        <div class="sm:col-span-2"><button type="submit" class="k-btn" id="imp-save">Impor</button></div>
    </form>
    <div id="imp-msg" class="k-result mt-3" role="status" aria-live="polite"></div>
    <details id="imp-detail" class="mt-3" hidden>
        <summary class="k-check">Lihat rincian per baris</summary>
        <ul id="imp-lines" class="body-md mt-2 flex flex-col gap-1"></ul>
    </details>
</section>
@endsection
