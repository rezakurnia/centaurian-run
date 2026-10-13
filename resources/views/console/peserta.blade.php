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
            <thead><tr><th>Nama</th><th>L/P</th><th>Email</th><th>Telepon</th><th>Daftar</th></tr></thead>
            <tbody id="p-body"><tr><td colspan="5">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>
@endsection
