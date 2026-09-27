<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pendaftaran</title>
</head>
<body>
    <h2>Halo, {{ $registration->participant->full_name }}</h2>
    <p>Terima kasih telah mendaftar di <strong>Centaurian FunRun</strong>.</p>

    <p>Berikut data peserta Anda:</p>
    <ul>
        <li><strong>Nomor Peserta:</strong> {{ $registration->registration_number }}</li>
        <li><strong>Barcode:</strong> {{ $registration->barcode }}</li>
        <li><strong>Kategori:</strong> {{ $registration->category->name ?? '-' }}</li>
        <li><strong>Paket:</strong> {{ $registration->package->name ?? '-' }}</li>
    </ul>

    <p>Tunjukkan barcode ini saat mencapai garis finis.</p>

    <p>Salam,<br>Panitia Centaurian FunRun</p>
</body>
</html>