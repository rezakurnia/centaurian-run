@extends('layouts.console')
@section('title', $title)
@section('content')
<section class="k-card" data-crud="{{ $resource }}">
    <div class="k-row"><button type="button" class="k-btn" id="crud-new">Tambah</button></div>
    <div id="crud-msg" class="k-result mt-3" role="status" aria-live="polite"></div>
    <div class="k-scroll mt-4">
        <table class="c-table">
            <thead id="crud-head"></thead>
            <tbody id="crud-body"><tr><td>Memuat…</td></tr></tbody>
        </table>
    </div>
</section>

<dialog id="crud-dialog" class="k-dialog" aria-labelledby="crud-title">
    <form id="crud-form" method="dialog" novalidate>
        <h2 class="k-h2" id="crud-title"></h2>
        <div id="crud-fields" class="flex flex-col gap-3"></div>
        <p class="c-error mt-3" id="crud-error" role="alert" hidden></p>
        <div class="k-row mt-5">
            <button type="submit" class="k-btn" id="crud-save">Simpan</button>
            <button type="button" class="k-btn -ghost" id="crud-cancel">Batal</button>
        </div>
    </form>
</dialog>

<dialog id="reset-dialog" class="k-dialog" aria-labelledby="reset-title">
    <form id="reset-form" method="dialog" novalidate>
        <h2 class="k-h2" id="reset-title">Reset acara</h2>
        <p class="body-md">Menghapus semua data scan dan hasil event ini. Tidak bisa dibatalkan.</p>
        <div class="c-field mt-4"><label class="c-label" for="reset-confirm">Ketik RESET untuk melanjutkan</label><input class="c-input" id="reset-confirm" autocomplete="off"></div>
        <div class="c-field mt-3"><label class="c-label" for="reset-password">Password kamu</label><input class="c-input" id="reset-password" type="password" autocomplete="current-password"></div>
        <p class="c-error mt-3" id="reset-error" role="alert" hidden></p>
        <div class="k-row mt-5">
            <button type="submit" class="k-btn" id="reset-save">Reset</button>
            <button type="button" class="k-btn -ghost" id="reset-cancel">Batal</button>
        </div>
    </form>
</dialog>
@endsection
