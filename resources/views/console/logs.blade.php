@extends('layouts.console')
@section('title', $title)
@section('content')
<section class="k-card" data-log="{{ $log }}">
    <form id="log-filter" class="k-row" aria-label="Filter log"></form>
    <div class="k-scroll mt-4" tabindex="0" role="region" aria-label="Tabel data">
        <table class="c-table">
            <thead id="log-head"></thead>
            <tbody id="log-body"><tr><td>Memuat…</td></tr></tbody>
        </table>
    </div>
    <div class="k-row mt-4 items-center">
        <button type="button" class="k-btn -ghost flex-1 sm:flex-none" id="log-prev">Sebelumnya</button>
        <span id="log-page" class="body-md order-last w-full text-center sm:order-none sm:w-auto" aria-live="polite"></span>
        <button type="button" class="k-btn -ghost flex-1 sm:flex-none" id="log-next">Berikutnya</button>
    </div>
</section>

{{-- Filled from GET {path}/{id} by logs.js --}}
<dialog id="log-dialog" class="k-dialog" aria-labelledby="log-dialog-title">
    <h2 class="k-h2" id="log-dialog-title">Detail log</h2>
    <dl id="log-dialog-body" class="k-dl"></dl>
    <div class="k-row mt-5"><button type="button" class="k-btn -ghost" id="log-close">Tutup</button></div>
</dialog>
@endsection
