@extends('layouts.console')
@section('title', 'Pendaftaran')
@section('content')
<section class="k-card">
    <form id="reg-filter" class="k-row" aria-label="Filter pendaftaran">
        <select class="c-input" name="category_id" aria-label="Kategori"><option value="">Semua kategori</option></select>
        <select class="c-input" name="registration_status" aria-label="Status pendaftaran">
            <option value="">Semua status</option><option value="pending">Menunggu</option><option value="confirmed">Terkonfirmasi</option><option value="cancelled">Dibatalkan</option>
        </select>
        <select class="c-input" name="payment_status" aria-label="Status bayar">
            <option value="">Semua pembayaran</option><option value="unpaid">Belum bayar</option><option value="paid">Lunas</option><option value="free">Gratis</option>
        </select>
    </form>
    <div class="k-scroll mt-4">
        <table class="c-table">
            <thead><tr><th>No.</th><th>Nama</th><th>Kategori</th><th>Paket</th><th>Pendaftaran</th><th>Bayar</th><th>Bukti</th><th></th></tr></thead>
            <tbody id="reg-body"><tr><td colspan="8">Memuat…</td></tr></tbody>
        </table>
    </div>
</section>

<dialog id="reg-dialog" class="k-dialog" aria-labelledby="reg-dialog-title">
    <form id="reg-verify" method="dialog">
        <h2 class="k-h2" id="reg-dialog-title"></h2>
        <p class="body-md" id="reg-dialog-info"></p>
        <p id="reg-dialog-proof" class="body-md mt-2"></p>
        <div class="c-field mt-4">
            <label class="c-label" for="v-reg">Status pendaftaran</label>
            <select class="c-input" id="v-reg" name="registration_status"><option value="pending">Menunggu</option><option value="confirmed">Terkonfirmasi</option><option value="cancelled">Dibatalkan</option></select>
        </div>
        <div class="c-field mt-3">
            <label class="c-label" for="v-pay">Status bayar</label>
            <select class="c-input" id="v-pay" name="payment_status"><option value="unpaid">Belum bayar</option><option value="paid">Lunas</option><option value="free">Gratis</option></select>
        </div>
        <p class="c-error mt-3" id="reg-dialog-error" role="alert" hidden></p>
        <div class="k-row mt-5">
            <button type="submit" class="k-btn" id="reg-save">Simpan &amp; kirim email</button>
            <button type="button" class="k-btn -ghost" id="reg-cancel">Batal</button>
        </div>
    </form>
</dialog>
@endsection
