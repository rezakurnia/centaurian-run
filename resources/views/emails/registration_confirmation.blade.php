<!DOCTYPE html>
<html>
<head>
    <title>Pendaftaran Diverifikasi</title>
</head>
<body>
    <h2>Halo, {{ $registration->participant->full_name }}</h2>

    <p>Kabar baik! Pendaftaran Anda di <strong>Centaurian FunRun</strong> telah <strong>diverifikasi</strong>.</p>

    <p>Berikut detail pendaftaran Anda:</p>
    <ul>
        <li><strong>Nomor Peserta:</strong> {{ $registration->registration_number }}</li>
        <li><strong>Barcode:</strong> {{ $registration->barcode }}</li>
        <li><strong>Kategori:</strong> {{ $registration->category->name ?? '-' }}</li>
        <li><strong>Paket:</strong> {{ $registration->package->name ?? '-' }}</li>
        <li><strong>Event:</strong> {{ $registration->event->name ?? '-' }}</li>
        <li><strong>Status Pendaftaran:</strong> {{ ucfirst($registration->registration_status) }}</li>
        <li><strong>Status Pembayaran:</strong> {{ ucfirst($registration->payment_status) }}</li>
    </ul>

    <p>Tunjukkan barcode ini saat mencapai garis finis.</p>

    <p>Salam,<br>Panitia Centaurian FunRun</p>
</body>
</html>