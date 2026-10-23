<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pendaftaran Terkonfirmasi</title>
</head>
<body>
    @php
        $regStatus = ['pending' => 'Menunggu', 'confirmed' => 'Terkonfirmasi', 'cancelled' => 'Dibatalkan'];
        $payStatus = ['unpaid' => 'Menunggu pembayaran', 'paid' => 'Lunas', 'free' => 'Gratis'];
    @endphp
    <h2>Halo, {{ $registration->participant->full_name }}</h2>

    <p>Kabar baik! Pendaftaranmu di <strong>Centaurian Run</strong> sudah <strong>terkonfirmasi</strong>.</p>

    <p>Berikut detail pendaftaranmu:</p>
    <ul>
        <li><strong>Nomor peserta:</strong> {{ $registration->registration_number }}</li>
        <li><strong>Barcode:</strong> {{ $registration->barcode }}</li>
        <li><strong>Kategori:</strong> {{ $registration->category->name ?? '-' }}</li>
        <li><strong>Paket:</strong> {{ $registration->package->name ?? '-' }}</li>
        <li><strong>Event:</strong> {{ $registration->event->name ?? '-' }}</li>
        <li><strong>Status pendaftaran:</strong> {{ $regStatus[$registration->registration_status] ?? $registration->registration_status }}</li>
        <li><strong>Status pembayaran:</strong> {{ $payStatus[$registration->payment_status] ?? $registration->payment_status }}</li>
    </ul>

    <p>Tunjukkan barcode ini saat pengambilan racepack dan check-in di hari acara.</p>

    <p>Salam,<br>Panitia Centaurian Run</p>
</body>
</html>
