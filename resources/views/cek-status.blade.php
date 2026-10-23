<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek BIB · Centaurian Run</title>
    <meta name="description" content="Cek nomor BIB peserta Centaurian Run. Kartu BIB resmi muncul untuk pendaftaran yang pembayarannya sudah lunas.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@php
    // Appended to the event name so the card reads "Centaurian Run 2026"
    $eventYear = app(\App\Services\SettingService::class)->getAllFlat()['event_year'] ?? '';
@endphp

@include('partials.nav', ['base' => '/'])

<main id="main" class="bg-beige">
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-32 pb-fluid-lg max-md:pt-28">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14 text-center">
                    <p class="body-md uppercase">Cek BIB</p>
                    <h1 class="heading-lg mx-auto mt-4 max-w-[22ch] text-balance">Cek nomor BIB-mu.</h1>
                    <p class="body-lg mx-auto mt-4 max-w-140 text-balance">Masukkan nomor peserta atau emailmu.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="py-fluid-2xl">
        <div class="container">
            {{-- max-w-md keeps the lookup field short and centred --}}
            <div class="mx-auto w-full max-w-md">
                <form id="status-form" class="c-step" novalidate>
                    <div class="c-field flex flex-col items-center text-center">
                        <label class="c-label mb-2" for="identifier">Nomor peserta atau email</label>
                        <input class="c-input text-center" id="identifier" name="identifier" type="text" autocomplete="email" autocapitalize="off" spellcheck="false" placeholder="Contoh: S-0001 atau nama@email.com" aria-describedby="err-identifier">
                        <p class="c-error" id="err-identifier" role="alert" hidden></p>
                    </div>

                    <div class="mt-6 flex justify-center">
                        @include('partials.btn', ['type' => 'submit', 'label' => 'Cek BIB', 'color' => 'green', 'primary' => true, 'attributes' => 'id="status-submit"'])
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Celebration card, cloned per lunas/gratis registration by c-check-status.js --}}
    <template id="bib-card" data-year="{{ $eventYear }}">
        <article class="c-step">
            <div class="rounded-sm bg-(image:--gradient-hero) p-fluid-lg text-center">
                <p class="body-md uppercase">Peserta Resmi</p>
                <h2 class="heading-sm mt-2">Selamat, kamu resmi ikut.</h2>
                <p class="body-lg mx-auto mt-3 max-w-140 text-balance">Kamu sudah terdaftar sebagai peserta <strong data-f="event"></strong>.</p>
            </div>

            <div class="mt-gutter grid gap-gutter md:grid-cols-[minmax(0,0.9fr)_1.1fr] md:items-center">
                <div class="rounded-sm bg-beige p-fluid-lg text-center">
                    <p class="body-md opacity-75">Nomor peserta (BIB)</p>
                    <p class="heading-md mt-2" data-f="number"></p>
                </div>
                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                    <dt class="opacity-75">Nama</dt><dd data-f="name"></dd>
                    <dt class="opacity-75">Kategori</dt><dd data-f="category"></dd>
                    <dt class="opacity-75">Paket</dt><dd data-f="package"></dd>
                </dl>
            </div>

            {{-- QR kartu masuk: drawn when the lookup included the email (the API only returns the barcode then); otherwise an email form asks for it --}}
            <div class="mt-gutter rounded-sm bg-beige p-fluid-lg text-center">
                <div data-f="qr-wrap" hidden>
                    <canvas data-f="qr" class="mx-auto max-w-full rounded-sm" role="img" aria-label="QR code kartu masuk"></canvas>
                    <p class="mt-4"><a class="c-link inline-block py-3 -my-3" data-f="qr-download" href="#" download="qr-peserta.png">Simpan QR</a></p>
                </div>
                <form data-f="qr-form" class="mx-auto max-w-sm" novalidate hidden>
                    <label class="c-label mb-2 block" data-f="qr-label">Email pendaftaran untuk menampilkan QR</label>
                    <input class="c-input text-center" data-f="qr-email" type="email" autocomplete="email" autocapitalize="off" spellcheck="false" placeholder="nama@email.com">
                    <p class="c-error justify-center" data-f="qr-error" role="alert" hidden></p>
                    <div class="mt-4 flex justify-center">
                        @include('partials.btn', ['type' => 'submit', 'label' => 'Tampilkan QR', 'color' => 'green', 'primary' => true])
                    </div>
                </form>
                <p class="body-md mt-4">Tunjukkan QR dan nomor BIB ini saat pengambilan racepack dan check-in di hari acara.</p>
            </div>
        </article>
    </template>

    {{-- Informational card for registrations that are not lunas yet --}}
    <template id="status-card">
        <article class="c-step">
            <p class="body-md opacity-75" data-f="event"></p>
            <h2 class="heading-md" data-f="number"></h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="c-badge" data-f="reg-badge"></span>
                <span class="c-badge" data-f="pay-badge"></span>
            </div>
            <dl class="mt-6 grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                <dt class="opacity-75">Kategori</dt><dd data-f="category"></dd>
                <dt class="opacity-75">Paket</dt><dd data-f="package"></dd>
                <dt class="opacity-75">Didaftarkan</dt><dd data-f="created"></dd>
            </dl>
            <p class="mt-4" data-f="pay-row" hidden><a class="c-link inline-block py-3 -my-3" data-f="pay-link" href="#" target="_blank" rel="noopener">Lanjutkan pembayaran →</a></p>
            <p class="mt-4" data-f="retry-row" hidden><button type="button" class="c-link inline-block py-3 -my-3" data-f="retry-btn">Bayar lagi →</button></p>
            <p class="c-error mt-2" data-f="retry-error" role="alert" hidden></p>
            <p class="body-md mt-5 rounded-sm bg-beige p-4" data-f="note"></p>
        </article>
    </template>

    {{-- The lookup result opens here as a pop-up instead of pushing the page down --}}
    <dialog id="status-dialog" class="c-dialog" aria-label="Hasil pengecekan BIB">
        <button type="button" class="c-dialog_close" id="status-close" aria-label="Tutup hasil pengecekan">
            <svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <div id="status-results" class="c-dialog_body"></div>
    </dialog>

    <div class="rounded-t-lg bg-green py-fluid-2xl">
        <div class="container">@include('partials.footer')</div>
    </div>
</main>

</body>
</html>