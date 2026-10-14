@extends('layouts.console')
@section('title', 'Ekspor data')
@section('content')
<section class="k-card" data-export>
    <form id="ex-form" class="k-row" aria-label="Filter ekspor">
        <select class="c-input" name="category_id" aria-label="Kategori"><option value="">Semua kategori</option></select>
        <select class="c-input" name="registration_status" aria-label="Status pendaftaran (hanya pendaftaran)">
            <option value="">Semua status</option><option value="pending">Menunggu</option><option value="confirmed">Terkonfirmasi</option><option value="cancelled">Dibatalkan</option>
        </select>
        <select class="c-input" name="payment_status" aria-label="Status bayar (hanya pendaftaran)">
            <option value="">Semua pembayaran</option><option value="unpaid">Belum bayar</option><option value="paid">Lunas</option><option value="free">Gratis</option>
        </select>
    </form>
    <p class="body-md mt-3">Filter status hanya berlaku untuk ekspor pendaftaran.</p>
    <div class="k-row mt-4">
        <button type="button" class="k-btn" data-ex="participants">Unduh peserta (CSV)</button>
        <button type="button" class="k-btn" data-ex="registrations">Unduh pendaftaran (CSV)</button>
    </div>
    <div id="ex-msg" class="k-result mt-3" role="status" aria-live="polite"></div>
</section>
@endsection
