@php
    $rupiah = fn (int $p) => $p === 0 ? 'Gratis' : 'Rp' . number_format($p, 0, ',', '.');
    $date = $event?->event_date?->locale('id')->translatedFormat('l, j F Y');
    $name = $event?->name ?? 'Centaurian Run ' . now()->year;
    $pickCategory = (int) request('category');
    $text = fn ($n, $l, $max, $type = 'text') => compact('n', 'l', 'max', 'type');
    // Field tambahan per kode kategori, sesuai StoreRegistrationRequest.
    $extra = [
        'S' => [$text('nis', 'NIS', 30), $text('class', 'Kelas', 30)],
        'E' => [$text('nip', 'NIP', 30), $text('position', 'Jabatan', 50), $text('unit', 'Unit', 50)],
        'P' => [$text('child_name', 'Nama anak', 100), $text('child_class', 'Kelas anak', 30)],
        'A' => [$text('graduation_year', 'Tahun lulus', 4, 'number')],
    ];
    $free = $categories->filter(fn ($c) => $c->package && (int) $c->package->price === 0)->pluck('name')->join(', ', ' dan ');
    $faqs = [
        ['Kategori mana yang gratis?', $free ? "Kategori {$free} gratis dan langsung terkonfirmasi; nomor peserta beserta barcode dikirim ke emailmu." : 'Saat ini semua kategori berbayar.'],
        ['Bagaimana membayar paket berbayar?', 'Setelah mendaftar, selesaikan pembayaran lalu unggah bukti pembayaran lewat halaman Cek status. Panitia memverifikasi, dan statusmu berubah menjadi terkonfirmasi.'],
        ['Emailnya tidak masuk, bagaimana?', 'Periksa folder spam dulu. Nomor pesertamu juga bisa dilihat kapan saja lewat Cek status memakai emailmu.'],
        ['Bisakah satu email mendaftar dua kali?', 'Tidak. Satu email hanya bisa terdaftar satu kali per event. Gunakan email lain untuk peserta lain.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar · {{ $name }}</title>
    <meta name="description" content="Pilih kategori dan amankan nomor pesertamu untuk {{ $name }}.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.png" type="image/png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

@include('partials.nav', ['base' => '/'])

<main class="bg-beige">
    {{-- Header --}}
    <header class="rounded-b-lg bg-(image:--gradient-hero) pt-32 pb-fluid-lg max-md:pt-28">
        <div class="container">
            <div class="grid grid-cols-6 gap-gutter md:grid-cols-16">
                <div class="col-span-full md:col-start-2 md:col-span-14 text-center">
                    <p class="body-md uppercase">Pendaftaran</p>
                    <h1 class="heading-lg mx-auto mt-4 max-w-[22ch] text-balance">Amankan nomor pesertamu.</h1>
                    <p class="body-lg mx-auto mt-4 max-w-140 text-balance">
                        Pilih kategori, lengkapi data kategori, isi datamu. Tiga langkah, beberapa menit.
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
                    @if (! $event || $closed)
                        <div class="rounded-sm bg-white p-fluid-lg">
                            <h2 class="heading-sm">{{ $event ? 'Pendaftaran ditutup.' : 'Pendaftaran belum dibuka.' }}</h2>
                            <p class="body-lg mt-3">{{ $event ? 'Pendaftaran untuk event ini sedang ditutup atau sudah melewati batas waktu.' : 'Belum ada event yang aktif.' }} Pantau terus halaman utama untuk pengumuman berikutnya.</p>
                            <div class="mt-6">@include('partials.btn', ['href' => '/', 'label' => 'Kembali ke beranda', 'color' => 'green', 'primary' => true])</div>
                        </div>
                    @else
                    <div class="grid items-start gap-gutter md:grid-cols-14">
                        <div class="md:col-span-9">

                            <form id="register-form" class="flex flex-col gap-gutter" novalidate data-event="{{ $event->id }}">
                                <input type="hidden" name="event_id" value="{{ $event->id }}">

                                {{-- 1. Kategori --}}
                                <fieldset class="c-step" aria-describedby="err-category_id">
                                    <legend class="c-step_legend"><span class="c-step_num" aria-hidden="true">1</span>Pilih kategori</legend>
                                    <div class="c-options">
                                        @foreach ($categories as $category)
                                            @php $full = $category->remaining === 0; @endphp
                                            <label class="c-option">
                                                <input type="radio" name="category_id" value="{{ $category->id }}" class="peer sr-only" data-name="{{ $category->name }}" data-code="{{ $category->code }}"
                                                    data-package="{{ $category->package?->name }}" data-price="{{ $category->package?->price }}" @checked(! $full && $pickCategory === $category->id) @disabled($full)>
                                                <span class="c-option_card"><span class="c-option_check" aria-hidden="true"></span>
                                                    <span class="c-option_title">{{ $category->name }}</span>
                                                    @if ($category->package)<span class="c-option_price">{{ $rupiah($category->package->price) }}</span>@endif
                                                    @if ($full)<span class="c-option_desc">Kuota penuh</span>
                                                    @elseif ($category->remaining !== null)<span class="c-option_desc">Sisa {{ $category->remaining }} slot</span>@endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="c-error" id="err-category_id" data-error="category_id" hidden></p>
                                </fieldset>

                                {{-- 2. Data kategori: grup yang tidak dipilih di-disable supaya tidak ikut terkirim --}}
                                <fieldset class="c-step">
                                    <legend class="c-step_legend"><span class="c-step_num" aria-hidden="true">2</span>Data kategori</legend>
                                    <p class="body-md" data-extra-empty>Pilih kategori dulu.</p>
                                    @foreach ($extra as $code => $fields)
                                        <fieldset class="grid gap-5 sm:grid-cols-2" data-extra="{{ $code }}" hidden disabled>
                                            @foreach ($fields as $f)
                                                <div class="c-field">
                                                    <label class="c-label" for="x-{{ $f['n'] }}">{{ $f['l'] }}</label>
                                                    <input class="c-input" id="x-{{ $f['n'] }}" name="{{ $f['n'] }}" type="{{ $f['type'] }}"
                                                        @if ($f['type'] === 'number') min="1900" max="{{ now()->year }}" inputmode="numeric" @else maxlength="{{ $f['max'] }}" @endif aria-describedby="err-{{ $f['n'] }}">
                                                    <p class="c-error" id="err-{{ $f['n'] }}" data-error="{{ $f['n'] }}" hidden></p>
                                                </div>
                                            @endforeach
                                        </fieldset>
                                    @endforeach
                                </fieldset>

                                {{-- 3. Data diri --}}
                                <fieldset class="c-step">
                                    <legend class="c-step_legend"><span class="c-step_num" aria-hidden="true">3</span>Data dirimu</legend>
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <div class="c-field sm:col-span-2">
                                            <label class="c-label" for="full_name">Nama lengkap</label>
                                            <input class="c-input" id="full_name" name="full_name" type="text" maxlength="100" autocomplete="name" aria-describedby="err-full_name">
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
                                        <div class="c-field sm:col-span-2">
                                            <label class="c-label" for="motivation">Motivasi <span class="opacity-75">(opsional)</span></label>
                                            <textarea class="c-input" id="motivation" name="motivation" rows="3" aria-describedby="err-motivation"></textarea>
                                            <p class="c-error" id="err-motivation" data-error="motivation" hidden></p>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="flex flex-col items-start gap-4">
                                    <p class="c-error c-error_form" id="form-error" role="alert" hidden></p>
                                    @include('partials.btn', ['type' => 'submit', 'label' => 'Kirim pendaftaran', 'color' => 'green', 'primary' => true, 'attributes' => 'id="register-submit"'])
                                </div>
                            </form>

                            {{-- Shown by c-register.js after a 201 --}}
                            <div id="register-success" class="c-step" tabindex="-1" hidden>
                                <h2 class="heading-md" id="success-title">Pendaftaran berhasil!</h2>
                                <p class="body-lg mt-4" id="success-message"></p>
                                <dl class="mt-6 grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                                    <dt class="opacity-75">Nomor peserta</dt><dd class="heading-xs" id="success-number"></dd>
                                    <dt class="opacity-75">Status</dt><dd id="success-status"></dd>
                                </dl>
                                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-sm bg-beige p-5" id="success-pay" hidden>
                                        <h3 class="heading-xs">Selesaikan pembayaran</h3>
                                        <p class="body-md mt-2">Unggah bukti pembayaran agar panitia bisa memverifikasi.</p>
                                        <div class="mt-4"><a id="success-pay-link" class="c-link" href="/cek-status">Unggah bukti bayar →</a></div>
                                    </div>
                                    <div class="rounded-sm bg-beige p-5">
                                        <h3 class="heading-xs">Pantau statusmu</h3>
                                        <p class="body-md mt-2">Cek kapan saja dengan nomor peserta atau emailmu.</p>
                                        <div class="mt-4"><a id="success-status-link" class="c-link" href="/cek-status">Cek status →</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Live summary: mirrors the estimator's result panel --}}
                        <aside class="c-step md:sticky md:top-28 md:col-span-5" aria-labelledby="summary-title">
                            <h2 class="heading-xs" id="summary-title">Ringkasan</h2>
                            <dl class="mt-5 divide-y divide-beige-500" aria-live="polite">
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Kategori</dt><dd id="sum-category" class="text-right">Belum dipilih</dd></div>
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Paket</dt><dd id="sum-package" class="text-right">Belum dipilih</dd></div>
                                <div class="flex justify-between gap-4 py-3"><dt class="opacity-75">Biaya</dt><dd id="sum-price" class="heading-xs text-right">–</dd></div>
                            </dl>
                            <p class="body-md mt-5 rounded-sm bg-beige p-4" id="sum-note">Paket ditentukan oleh kategori. Paket gratis langsung terkonfirmasi. Paket berbayar: transfer, unggah bukti, lalu tunggu verifikasi panitia.</p>
                        </aside>
                    </div>

                    <div class="mt-fluid-2xl">
                        <h2 class="heading-sm">Pertanyaan umum</h2>
                        <div class="mt-6 divide-y divide-beige-500 rounded-sm bg-white px-5">
                            @foreach ($faqs as [$q, $a])
                                <details class="c-faq py-4">
                                    <summary class="heading-xs cursor-pointer">{{ $q }}</summary>
                                    <p class="body-lg mt-3 max-w-160">{{ $a }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="rounded-t-lg bg-green py-fluid-2xl">
        <div class="container">@include('partials.footer')</div>
    </div>
</main>

</body>
</html>
