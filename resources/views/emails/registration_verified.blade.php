<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pendaftaran Terkonfirmasi</title>
</head>
<body>
    <h2>Halo, {{ $registration->participant->full_name }}</h2>

    <p>
        @if ($registration->payment_status === 'free')
            Pendaftaranmu di <strong>Centaurian Run</strong> sudah <strong>terkonfirmasi</strong>.
        @else
            Pembayaran paket <strong>{{ $registration->package->name ?? '-' }}</strong> sudah kami terima.
            Pendaftaranmu di <strong>Centaurian Run</strong> sudah <strong>terkonfirmasi</strong>.
        @endif
    </p>

    <p>Berikut detail pendaftaranmu:</p>
    <ul>
        <li><strong>Nomor peserta:</strong> {{ $registration->registration_number }}</li>
        <li><strong>Barcode:</strong> {{ $registration->barcode }}</li>
        <li><strong>Kategori:</strong> {{ $registration->category->name ?? '-' }}</li>
        <li><strong>Paket:</strong> {{ $registration->package->name ?? '-' }}</li>
        <li><strong>Event:</strong> {{ $registration->event->name ?? '-' }}</li>
        <li><strong>Status pembayaran:</strong> {{ $registration->payment_status === 'free' ? 'Gratis' : 'Lunas' }}</li>
    </ul>

    <p>Tunjukkan barcode ini saat pengambilan racepack dan check-in di hari acara.</p>

    <p>Salam,<br>Panitia Centaurian Run</p>
</body>
</html>
