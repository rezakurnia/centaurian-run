<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran · Centaurian Run</title>
    <meta name="description" content="Status pembayaran pendaftaran Centaurian Run.">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@php
    $paid = $registration && in_array($registration->payment_status, ['paid', 'free'], true);
@endphp

@include('partials.nav', ['base' => '/'])

<main id="main" class="bg-beige">
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-32 pb-fluid-lg max-md:pt-28">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full text-center md:col-start-2 md:col-span-14">
                    <p class="body-md uppercase">Pembayaran</p>
                    <h1 class="heading-lg mx-auto mt-4 max-w-[22ch] text-balance">
                        {{ $paid ? 'Pembayaranmu berhasil.' : 'Selesaikan pembayaranmu.' }}
                    </h1>
                </div>
            </div>
        </div>
    </header>

    <section class="py-fluid-2xl">
        <div class="container">
            <div class="mx-auto w-full max-w-3xl">
                @if (! $registration)
                    {{-- Unknown participant number --}}
                    <article class="c-step">
                        <h2 class="heading-md">Pendaftaran tidak ditemukan.</h2>
                        <p class="body-lg mt-4">Periksa kembali tautannya, atau cek status dengan nomor peserta/emailmu di halaman Cek BIB.</p>
                        <p class="mt-6"><a class="c-link inline-block py-3 -my-3" href="/cek-status">Buka Cek BIB →</a></p>
                    </article>
                @elseif ($paid)
                    {{-- The participant number only appears once the payment is settled --}}
                    <article class="c-step">
                        <div class="rounded-sm bg-(image:--gradient-hero) p-fluid-lg text-center">
                            <p class="body-md uppercase">Peserta Resmi</p>
                            <h2 class="heading-sm mt-2">Pendaftaran berhasil.</h2>
                            <p class="body-lg mx-auto mt-3 max-w-140 text-balance">
                                {{ $registration->payment_status === 'free'
                                    ? 'Pendaftaranmu sudah terkonfirmasi sebagai peserta.'
                                    : 'Pembayaran untuk ' . ($registration->package->name ?? 'paketmu') . ' sudah kami terima.' }}
                            </p>
                            <div class="mt-4 flex justify-center">
                                <span class="c-badge -ok">{{ $registration->payment_status === 'free' ? 'Gratis' : 'Lunas' }}</span>
                            </div>
                        </div>

                        <div class="mt-gutter grid gap-gutter md:grid-cols-[minmax(0,0.9fr)_1.1fr] md:items-center">
                            <div class="rounded-sm bg-beige p-fluid-lg text-center">
                                <p class="body-md opacity-75">Nomor peserta</p>
                                <p class="heading-md mt-2">{{ $registration->registration_number }}</p>
                            </div>
                            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                                <dt class="opacity-75">Nama</dt><dd>{{ $registration->participant->full_name }}</dd>
                                <dt class="opacity-75">Kategori</dt><dd>{{ $registration->category->name }}</dd>
                                <dt class="opacity-75">Paket</dt><dd>{{ $registration->package->name }}</dd>
                            </dl>
                        </div>

                        <p class="body-md mt-gutter rounded-sm bg-beige p-4">Nomor peserta dan barcode juga dikirim ke emailmu.</p>
                        <p class="mt-6 flex flex-wrap gap-x-6 gap-y-2">
                            {{-- Shown by c-payment-finish.js only in the browser that registered; the barcode never comes from the server here --}}
                            <button type="button" class="c-link inline-block py-3 -my-3" id="barcode-open" data-number="{{ $registration->registration_number }}" hidden>Lihat QR kartu masuk</button>
                            <a class="c-link inline-block py-3 -my-3" href="/cek-status?no={{ rawurlencode($registration->registration_number) }}">Lihat kartu BIB →</a>
                        </p>
                    </article>
                @else
                    {{-- Waiting for the Midtrans notification; c-payment-finish.js reloads when it lands --}}
                    <article class="c-step text-center" id="payment-waiting" data-number="{{ $registration->registration_number }}">
                        <p class="body-md uppercase">Menunggu pembayaran</p>
                        <h2 class="heading-md mt-2">Pembayaran sedang kami verifikasi…</h2>
                        <p class="body-lg mx-auto mt-4 max-w-120 text-balance">Halaman ini diperbarui otomatis begitu pembayaranmu terkonfirmasi. Nomor peserta muncul setelah pembayaran berhasil.</p>

                        <dl class="mx-auto mt-6 grid w-full max-w-sm grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-left">
                            <dt class="opacity-75">Kategori</dt><dd>{{ $registration->category->name }}</dd>
                            <dt class="opacity-75">Paket</dt><dd>{{ $registration->package->name }}</dd>
                        </dl>

                        <p class="body-md mt-6" id="payment-slow" role="status" hidden>Pembayaran belum terkonfirmasi. Kalau kamu menutup halaman pembayaran, buka lagi lewat tautan di bawah, lalu muat ulang halaman ini.</p>

                        <div class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-2">
                            @if ($registration->snap_redirect_url)
                                <a class="c-link inline-block py-3 -my-3" href="{{ $registration->snap_redirect_url }}">Lanjutkan pembayaran →</a>
                            @endif
                            <a class="c-link inline-block py-3 -my-3" href="/pembayaran/selesai/{{ rawurlencode($registration->registration_number) }}">Muat ulang halaman</a>
                            <a class="c-link inline-block py-3 -my-3" href="/cek-status">Cek BIB</a>
                        </div>
                    </article>
                @endif
            </div>
        </div>
    </section>

    @include('partials.barcode-dialog')

    <div class="rounded-t-lg bg-green py-fluid-2xl">
        <div class="container">@include('partials.footer')</div>
    </div>
</main>

</body>
</html>
