<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek status · Centaurian Run</title>
    <meta name="description" content="Cek status pendaftaran dan unggah bukti pembayaran Centaurian Run.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@include('partials.nav', ['base' => '/'])

<main class="bg-beige">
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-32 pb-fluid-lg max-md:pt-28">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14 text-center">
                    <p class="body-md uppercase">Cek status</p>
                    <h1 class="heading-lg mx-auto mt-4 max-w-[22ch] text-balance">Sudah sampai mana?</h1>
                    <p class="body-lg mx-auto mt-4 max-w-140 text-balance">Masukkan nomor peserta atau emailmu. Paket berbayar bisa langsung mengunggah bukti pembayaran di sini.</p>
                </div>
            </div>
        </div>
    </header>

    <section class="py-fluid-2xl">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14">
                    <div class="grid items-start gap-gutter md:grid-cols-14">
                        <div class="md:col-span-9">
                            <form id="status-form" class="c-step" novalidate>
                                <div class="c-field">
                                    <label class="c-label" for="identifier">Nomor peserta atau email</label>
                                    <input class="c-input" id="identifier" name="identifier" type="text" autocomplete="email" autocapitalize="off" spellcheck="false" placeholder="mis. SW-0001 atau nama@email.com" aria-describedby="err-identifier">
                                    <p class="c-error" id="err-identifier" hidden></p>
                                </div>
                                <div class="mt-6">
                                    @include('partials.btn', ['type' => 'submit', 'label' => 'Cek status', 'color' => 'green', 'primary' => true, 'attributes' => 'id="status-submit"'])
                                </div>
                            </form>

                            <div id="status-results" class="mt-gutter flex flex-col gap-gutter" aria-live="polite"></div>
                        </div>

                        <aside class="c-step md:sticky md:top-28 md:col-span-5">
                            <h2 class="heading-xs">Belum mendaftar?</h2>
                            <p class="body-md mt-3">Pilih kategori dan paket, lalu isi datamu. Cuma beberapa menit.</p>
                            <div class="mt-5"><a class="c-link" href="/daftar">Daftar sekarang →</a></div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- One result card, cloned per registration by c-check-status.js --}}
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
            <p class="body-md mt-5 rounded-sm bg-beige p-4" data-f="note"></p>

            <form class="mt-6 flex flex-col gap-4" data-f="upload" novalidate hidden>
                <div class="c-field">
                    <label class="c-label" data-f="file-label">Bukti pembayaran (JPG, PNG, atau PDF, maks. 2 MB)</label>
                    <input class="c-input c-file" type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" data-f="file">
                    <p class="c-error" data-f="upload-error" hidden></p>
                </div>
                <div>
                    @include('partials.btn', ['type' => 'submit', 'label' => 'Unggah bukti bayar', 'color' => 'green', 'primary' => true, 'attributes' => 'data-f="upload-submit"'])
                </div>
            </form>
            <p class="body-md mt-4" data-f="proof" hidden>Bukti pembayaran: <a class="c-link" data-f="proof-link" target="_blank" rel="noopener">lihat berkas</a></p>
        </article>
    </template>

    <div class="rounded-t-lg bg-green py-fluid-2xl">
        <div class="container">@include('partials.footer')</div>
    </div>
</main>

</body>
</html>
