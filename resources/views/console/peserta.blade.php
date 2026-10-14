@extends('layouts.console')
@section('title', 'Peserta')
@section('content')
<section class="k-card">
    <form id="p-filter" class="k-row" aria-label="Filter peserta">
        <input class="c-input" name="search" type="search" placeholder="Cari nama, email, atau nomor" aria-label="Cari peserta" autocomplete="off">
        <select class="c-input" name="category_id" aria-label="Kategori"><option value="">Semua kategori</option></select>
        <button type="button" class="k-btn" id="imp-open">Impor Excel</button>
    </form>
    <div class="k-scroll mt-4">
        <table class="c-table">
            <thead><tr><th>Nama</th><th>L/P</th><th>Email</th><th>Telepon</th><th>Daftar</th></tr></thead>
            <tbody id="p-body"><tr><td colspan="5">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>

<dialog id="imp-dialog" class="k-dialog" aria-labelledby="imp-title">
    <form id="imp-form" method="dialog" novalidate>
        <h2 class="k-h2" id="imp-title">Impor peserta</h2>
        <p class="body-md">Peserta impor langsung terkonfirmasi dan gratis. Unduh template dulu:
            <button type="button" class="c-link" data-tpl="siswa">template siswa</button> ·
            <button type="button" class="c-link" data-tpl="guru">template guru</button></p>
        <div class="c-field mt-4"><label class="c-label" for="imp-type">Jenis</label>
            <select class="c-input" id="imp-type" name="type"><option value="siswa">Siswa</option><option value="guru">Guru / karyawan</option></select></div>
        <div class="c-field mt-3"><label class="c-label" for="imp-event">Event</label><select class="c-input" id="imp-event" name="event_id"></select></div>
        <div class="c-field mt-3"><label class="c-label" for="imp-package">Paket</label><select class="c-input" id="imp-package" name="package_id"></select></div>
        <div class="c-field mt-3"><label class="c-label" for="imp-file">Berkas Excel (.xlsx/.xls, maks. 5 MB)</label>
            <input class="c-input c-file" id="imp-file" name="file" type="file" accept=".xlsx,.xls"></div>
        <p class="c-error mt-3" id="imp-error" role="alert" hidden></p>
        <div id="imp-result" class="k-result mt-3" role="status" aria-live="polite"></div>
        <div class="k-row mt-5">
            <button type="submit" class="k-btn" id="imp-save">Impor</button>
            <button type="button" class="k-btn -ghost" id="imp-cancel">Tutup</button>
        </div>
    </form>
</dialog>
@endsection
