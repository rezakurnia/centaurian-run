@extends('layouts.console')
@section('title', $title)
@section('content')
<section class="k-card" data-log="{{ $log }}">
    <form id="log-filter" class="k-row" aria-label="Filter log"></form>
    <div class="k-scroll mt-4">
        <table class="c-table">
            <thead id="log-head"></thead>
            <tbody id="log-body"><tr><td>Memuat…</td></tr></tbody>
        </table>
    </div>
    <div class="k-row mt-4 items-center">
        <button type="button" class="k-btn -ghost" id="log-prev">Sebelumnya</button>
        <span id="log-page" class="body-md" aria-live="polite"></span>
        <button type="button" class="k-btn -ghost" id="log-next">Berikutnya</button>
    </div>
</section>
@endsection
