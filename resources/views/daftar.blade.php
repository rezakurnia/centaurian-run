@php
    $date = $event?->event_date?->locale('id')->translatedFormat('l, j F Y');
    $name = $event?->name ?? 'Centaurian Run ' . now()->year;
    $pickCategory = (int) request('category');
    // Extra fields the API requires per category code (StoreRegistrationRequest)
    $extra = [
        'S' => [['nis', 'NIS', 'text', 30], ['class', 'Kelas', 'text', 30]],
        'E' => [['nip', 'NIP', 'text', 30], ['position', 'Jabatan', 'text', 50], ['unit', 'Unit', 'text', 50]],
        'P' => [['child_name', 'Nama anak', 'text', 100], ['child_class', 'Kelas anak', 'text', 30]],
    ];
    $faqs = [
        ['Apakah paket gratis langsung terkonfirmasi?', 'Ya. Paket gratis langsung berstatus terkonfirmasi, dan nomor peserta beserta barcode dikirim ke emailmu.'],
        ['Bagaimana membayar paket berbayar?', 'Setelah mendaftar, selesaikan pembayaran sesuai instruksi yang muncul. Begitu pembayaranmu lunas, kartu BIB langsung terbuka di halaman Cek BIB.'],
        ['Emailnya tidak masuk, bagaimana?', 'Periksa folder spam dulu. Nomor pesertamu juga bisa dilihat kapan saja lewat halaman Cek BIB dengan nomor peserta atau emailmu.'],
        ['Bisakah satu email mendaftar dua kali?', 'Tidak. Satu email hanya bisa terdaftar satu kali per event. Gunakan email lain untuk peserta lain.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar · {{ $name }}</title>
    <meta name="description" content="Pilih kategori, isi datamu, dan amankan nomor pesertamu untuk {{ $name }}.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@include('partials.nav', ['base' => '/'])

<main id="main" class="bg-beige">
    {{-- Header --}}
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-32 pb-fluid-lg max-md:pt-28">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14 text-center">
                    <p class="body-md uppercase">Pendaftaran</p>
                    <h1 class="heading-lg mx-auto mt-4 max-w-[22ch] text-balance">Amankan nomor pesertamu.</h1>
                    <p class="body-lg mx-auto mt-4 max-w-140 text-balance">
                        Pilih kategori, isi datamu. Dua langkah, beberapa menit.
                        @if ($event) <br>{{ $name }} · {{ $date }}@if ($event->location) · {{ $event->location }}@endif @endif
                    </p>
                </div>
            </div>
        </div>
    </header>

    <section class="py-fluid-2xl">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14">
                    @if (! $event)
                        <div class="rounded-sm bg-white p-fluid-lg">
                            <h2 class="heading-sm">Pendaftaran belum dibuka.</h2>
                            <p class="body-lg mt-3">Belum ada event yang aktif. Pantau terus halaman utama untuk pengumuman berikutnya.</p>
                            <div class="mt-6">@include('partials.btn', ['href' => '/', 'label' => 'Kembali ke beranda', 'color' => 'green', 'primary' => true])</div>
                        </div>
                    @else
                    <div class="grid items-start gap-gutter md:grid-cols-14">
                        <div class="md:col-span-9">

                            {{-- Shown by c-register.js when the registration_open / registration_deadline settings close it --}}
                            <div id="register-closed" class="c-step mb-gutter" role="status" hidden>
                                <h2 class="heading-sm">Pendaftaran sedang ditutup.</h2>
                                <p class="body-lg mt-3" id="register-closed-text">Pantau terus halaman utama untuk pengumuman berikutnya.</p>
                            </div>

                            <form id="register-form" class="flex flex-col gap-gutter" novalidate>
                                <input type="hidden" name="event_id" value="{{ $event->id }}">

                                {{-- 1. Kategori --}}
                                <fieldset class="c-step" aria-describedby="err-category_id">
                                    <legend class="c-step_legend"><span class="c-step_num" aria-hidden="true">1</span>Pilih kategori</legend>
                                    <div class="c-options">
                                        @foreach ($categories as $category)
                                            @php $package = $category->package; @endphp
                                            <label class="c-option">
                                                <input type="radio" name="category_id" value="{{ $category->id }}" class="peer sr-only" data-name="{{ $category->name }}" data-code="{{ $category->code }}" data-package="{{ $package?->name }}" data-price="{{ $package?->price }}" @checked($pickCategory === $category->id)>
                                                <span class="c-option_card"><span class="c-option_check" aria-hidden="true"></span>
                                                    <span class="c-option_title">{{ $category->name }}</span>
                                                    <span class="c-option_badge" data-full hidden><span class="c-badge -bad">Penuh</span></span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="c-error" id="err-category_id" data-error="category_id" hidden></p>
                                </fieldset>

                                {{-- 2. Data diri --}}
                                <fieldset class="c-step">
                                    <legend class="c-step_legend"><span class="c-step_num" aria-hidden="true">2</span>Data dirimu</legend>
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <div class="c-field sm:col-span-2">
                                            <label class="c-label" for="full_name">Nama lengkap</label>
                                            <input class="c-input" id="full_name" name="full_name" type="text" maxlength="100" autocomplete="name" aria-describedby="err-full_name">
                                            {{-- Shown by c-register.js once a Guru Karyawan NIP matches the employee master --}}
                                            <p class="c-hint" id="name-note" hidden>Terisi otomatis dari data pegawai dan tidak dapat diubah.</p>
                                            <p class="c-error" id="err-full_name" data-error="full_name" hidden></p>
                                        </div>
                                        <div class="c-field">
                                            <label class="c-label" for="email">Email</label>
                                            <input class="c-input" id="email" name="email" type="email" maxlength="100" autocomplete="email" aria-describedby="err-email">
                                            <p class="c-error" id="err-email" data-error="email" hidden></p>
                                        </div>
                                        <div class="c-field">
                                            <label class="c-label" for="phone">Nomor telepon</label>
                                            <input class="c-input" id="phone" name="phone" type="tel" inputmode="tel" maxlength="20" autocomplete="tel" aria-describedby="err-phone">
                                            <p class="c-error" id="err-phone" data-error="phone" hidden></p>
                                        </div>
                                        <div class="c-field">
                                            <label class="c-label" for="birth_place">Tempat lahir</label>
                                            <input class="c-input" id="birth_place" name="birth_place" type="text" maxlength="100" aria-describedby="err-birth_place">
                                            <p class="c-error" id="err-birth_place" data-error="birth_place" hidden></p>
                                        </div>
                                        <div class="c-field">
                                            <label class="c-label" for="birth_date">Tanggal lahir</label>
                                            <input class="c-input" id="birth_date" name="birth_date" type="date" max="{{ now()->toDateString() }}" autocomplete="bday" aria-describedby="err-birth_date">
                                            <p class="c-error" id="err-birth_date" data-error="birth_date" hidden></p>
                                        </div>
                                        <fieldset class="c-field sm:col-span-2" aria-describedby="err-gender">
                                            <legend class="c-label">Jenis kelamin</legend>
                                            <div class="grid grid-cols-2 gap-3 sm:max-w-80">
                                                @foreach (['L' => 'Laki-laki', 'P' => 'Perempuan'] as $val => $label)
                                                    <label class="c-option">
                                                        <input type="radio" name="gender" value="{{ $val }}" class="peer sr-only">
                                                        <span class="c-option_card -compact"><span class="c-option_check" aria-hidden="true"></span><span class="c-option_title">{{ $label }}</span></span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <p class="c-error" id="err-gender" data-error="gender" hidden></p>
                                        </fieldset>
                                        {{-- Shown by c-register.js for the checked category; disabled fields are not submitted --}}
                                        @foreach ($extra as $code => $fields)
                                            <fieldset class="grid gap-5 sm:col-span-2 sm:grid-cols-2" data-for="{{ $code }}" hidden disabled>
                                                @foreach ($fields as [$field, $label, $type, $max])
                                                    <div class="c-field">
                                                        <label class="c-label" for="{{ $field }}">{{ $label }}</label>
                                                        <input class="c-input" id="{{ $field }}" name="{{ $field }}" type="text" maxlength="{{ $max }}" @if ($field === 'nip') placeholder="Contoh: 14000012" inputmode="numeric" autocomplete="off" aria-describedby="nip-hint err-nip" @else aria-describedby="err-{{ $field }}" @endif>
                                                        @if ($field === 'nip')
                                                            {{-- Checked live against GET /api/employees/{nip} by c-register.js --}}
                                                            <p class="c-hint" id="nip-hint">NIP harus terdaftar sebagai guru/karyawan Alcent. Nama terisi otomatis setelah NIP cocok.</p>
                                                            <p class="c-note" id="nip-note" role="status" hidden></p>
                                                        @endif
                                                        <p class="c-error{{ $field === 'nip' ? ' -nip' : '' }}" id="err-{{ $field }}" data-error="{{ $field }}" hidden></p>
                                                    </div>
                                                @endforeach
                                            </fieldset>
                                        @endforeach
                                        <div class="c-field sm:col-span-2">
                                            <label class="c-label" for="motivation">Motivasi <span class="opacity-75">(opsional)</span></label>
                                            <textarea class="c-input" id="motivation" name="motivation" rows="3" aria-describedby="err-motivation"></textarea>
                                            <p class="c-error" id="err-motivation" data-error="motivation" hidden></p>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="flex flex-col items-start gap-4">
                                    <p class="c-error c-error_form" id="form-error" role="alert" hidden></p>
                                    <p class="body-md">Dengan mengirim, kamu menyetujui <a class="c-link" href="/#tata-tertib">tata tertib acara</a>.</p>
                                    @include('partials.btn', ['type' => 'submit', 'label' => 'Kirim pendaftaran', 'color' => 'green', 'primary' => true, 'attributes' => 'id="register-submit"'])
                                </div>
                            </form>

                            {{-- Shown for a paid package: no participant number yet, the browser goes straight to Snap --}}
                            <div id="register-redirect" class="c-step" tabindex="-1" hidden>
                                <h2 class="heading-md">Mengarahkan ke pembayaran…</h2>
                                <p class="body-lg mt-4">Kamu akan diarahkan ke halaman pembayaran Midtrans. Jangan tutup halaman ini.</p>
                                <p class="mt-6" id="register-redirect-link" hidden>
                                    <a class="c-link inline-block py-3 -my-3" id="register-redirect-href" href="#">Buka halaman pembayaran →</a>
                                </p>
                            </div>

                            {{-- Shown by c-register.js after a 201 for free packages, or when Snap is unavailable --}}
                            <div id="register-success" class="c-step" tabindex="-1" hidden>
                                <h2 class="heading-md" id="success-title">Pendaftaran berhasil.</h2>
                                <p class="body-lg mt-4" id="success-message"></p>
                                <dl class="mt-6 grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                                    <dt class="opacity-75">Nomor peserta</dt><dd class="heading-xs" id="success-number"></dd>
                                    <dt class="opacity-75">Status</dt><dd id="success-status"></dd>
                                </dl>
                                <p class="mt-6"><button type="button" class="c-link inline-block py-3 -my-3" id="success-qr" hidden>Lihat QR kartu masuk</button></p>
                                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-sm bg-beige p-5" id="success-pay" hidden>
                                        <h3 class="heading-xs">Pembayaran belum tersedia</h3>
                                        {{-- Shown when the Snap transaction could not be created (paid package, no redirect URL) --}}
                                        <p class="body-md mt-2">Halaman pembayaran belum bisa disiapkan. Pendaftaranmu tersimpan; hubungi panitia lewat kontak di bagian bawah halaman untuk menyelesaikan pembayaran.</p>
                                    </div>
                                    <div class="rounded-sm bg-beige p-5">
                                        <h3 class="heading-xs">Pantau statusmu</h3>
                                        <p class="body-md mt-2">Cek kapan saja dengan nomor peserta atau emailmu.</p>
                                        <div class="mt-4"><a id="success-status-link" class="c-link inline-block py-3 -my-3" href="/cek-status">Cek BIB →</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Live summary: mirrors the estimator's result panel --}}
                        <aside class="c-step max-md:order-first md:sticky md:top-28 md:col-span-5" aria-labelledby="summary-title">
                            <h2 class="heading-xs" id="summary-title">Ringkasan</h2>
                            <dl class="mt-5 divide-y divide-beige-500" aria-live="polite">
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Kategori</dt><dd id="sum-category" class="text-right">Belum dipilih</dd></div>
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Paket</dt><dd id="sum-package" class="text-right">Ikut kategori</dd></div>
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Biaya</dt><dd id="sum-price" class="heading-xs text-right">–</dd></div>
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Kuota</dt><dd id="sum-quota" class="text-right">–</dd></div>
                            </dl>
                            <p class="body-md mt-5 rounded-sm bg-beige p-4" id="sum-note">Paket gratis langsung terkonfirmasi. Paket berbayar: kamu diarahkan ke halaman pembayaran, dan kartu BIB terbuka begitu pembayaranmu lunas.</p>
                        </aside>
                    </div>

                    <div class="mt-fluid-2xl">
                        <h2 class="heading-sm">Pertanyaan umum</h2>
                        <div class="mt-6 divide-y divide-beige-500 rounded-sm bg-white px-5">
                            @foreach ($faqs as [$q, $a])
                                <details class="c-faq open:pb-4">
                                    <summary class="heading-xs cursor-pointer py-4">{{ $q }}</summary>
                                    <p class="body-lg max-w-160">{{ $a }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
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
