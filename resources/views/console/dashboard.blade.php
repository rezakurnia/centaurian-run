@extends('layouts.console')
@section('title', 'Dashboard')
@section('content')
<div class="k-stats" id="dash-stats" aria-live="polite"></div>
<section class="k-card">
    <h2 class="k-h2">Pendaftaran per kategori</h2>
    <div class="k-scroll" tabindex="0" role="region" aria-label="Tabel data">
        <table class="c-table">
            <thead><tr><th>Kategori</th><th>Kode</th><th>Total</th></tr></thead>
            <tbody id="dash-category"><tr><td colspan="3">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>
@endsection
