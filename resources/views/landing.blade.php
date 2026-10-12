@php
    $c = $contents;
    $rupiah = fn (int $p) => $p === 0 ? 'Gratis' : 'Rp' . number_format($p, 0, ',', '.');
    $date = $event?->event_date?->locale('id')->translatedFormat('l, j F Y');
    $time = $event?->start_time?->format('H:i');
    $minPrice = $packages->min('price');
    $ctaTitle = $c->get('cta')?->title ?? "Siap lari\nbareng?";

    $cards = [
        [
            'id' => 'kategori', 'bg' => 'var(--color-red)', 'href' => '/daftar', 'button' => 'Daftar',
            'pos' => '--top:23.1%; --left:59.5vw',
            'title' => 'Semua bisa lari.',
            'body' => 'Siswa, guru, alumni, orang tua, sampai tamu dari luar. Centaurian Run terbuka untuk semua kalangan, pilih kategorimu saat mendaftar.',
        ],
        [
            'id' => 'paket', 'bg' => 'var(--color-yellow)', 'href' => '/daftar', 'button' => 'Pilih paket',
            'pos' => '--top:36.9%; --left:11.25vw',
            'title' => 'Pilih paketmu.',
            'body' => 'Mulai dari gratis. Untuk paket berbayar, cukup transfer, unggah bukti pembayaran, dan tunggu verifikasi panitia.',
        ],
        [
            'id' => 'jadwal', 'bg' => 'var(--color-blue)', 'href' => '/cek-status', 'button' => 'Cek status',
            'pos' => '--top:54.6%; --left:35.9vw',
            'title' => 'Catat tanggalnya.',
            'body' => 'Datang lebih awal dan pemanasan bareng. Cek status pendaftaranmu kapan saja dengan nomor registrasi.',
        ],
        [
            'id' => 'hasil', 'bg' => 'var(--color-pink)', 'href' => '/hasil', 'button' => 'Hasil lomba',
            'pos' => '--top:74.5%; --left:59.5vw',
            'title' => 'Lihat hasilmu.',
            'body' => 'Waktu tempuh dicatat dari scan start dan finish. Begitu kamu melewati garis finish, hasilmu langsung tercatat.',
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event?->name ?? 'Centaurian Run' }}</title>
    <meta name="description" content="{{ Str::limit($event?->description ?? 'Fun run keluarga besar Alfa Centauri.', 155) }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

{{-- Nav --}}
<header class="nav fixed inset-x-4 top-4 z-50 mx-auto flex max-w-[1400px] gap-5">
    <div class="flex h-16 grow items-center justify-between rounded-sm bg-white pr-2.5 pl-4">
        <a href="/" class="flex items-center gap-3 text-[1.5rem] tracking-[-0.05em]">
            <svg width="36" height="36" viewBox="0 0 36 36" aria-hidden="true">
                <path d="M30 11a14 14 0 1 0 1 13" fill="none" stroke="var(--color-green)" stroke-width="5" stroke-linecap="round"/>
                <path d="M11 22c3-6 7-8 13-8" fill="none" stroke="var(--color-charcoal)" stroke-width="3" stroke-linecap="round"/>
                <circle cx="27" cy="14" r="3" fill="var(--color-charcoal)"/>
            </svg>
            Centaurian Run
        </a>
        <nav class="hidden items-center gap-1 md:flex" aria-label="Utama">
            @foreach ($cards as $card)
                <a href="#{{ $card['id'] }}" class="nav-link"><span>{{ ucfirst($card['id']) }}</span></a>
            @endforeach
        </nav>
        <details class="relative md:hidden">
            <summary class="grid size-11 cursor-pointer list-none place-items-center rounded-full bg-green" aria-label="Menu">
                <svg width="22" height="22" viewBox="0 0 22 22" aria-hidden="true"><path d="M5 8h12M5 14h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </summary>
            <div class="absolute top-14 right-0 flex w-56 flex-col rounded-sm bg-white p-2 shadow-lg">
                @foreach ($cards as $card)
                    <a href="#{{ $card['id'] }}" class="nav-link"><span>{{ ucfirst($card['id']) }}</span></a>
                @endforeach
                <a href="/daftar" class="nav-link"><span>Daftar</span></a>
            </div>
        </details>
    </div>
    <a href="/daftar" class="nav-cta relative hidden h-16 items-center gap-4 rounded-sm bg-white pr-2.5 pl-5 text-[1.0625rem] md:flex">
        <span>Daftar sekarang</span>
        <span class="grid size-10 place-items-center rounded-full bg-green">
            <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="m8 14 4-4-4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
    </a>
</header>

<main>
    <div class="relative">
        {{-- Hero: sticks while the timeline slides over it --}}
        <section class="hero sticky top-0 flex h-[calc(100svh+50px)] flex-col items-center overflow-hidden bg-green px-4 pt-[clamp(130px,24vh,230px)] text-center">
            <h1 class="heading-2xl max-w-[12ch] text-balance" data-reveal>{{ $event?->name ?? 'Centaurian Run' }}</h1>
            <p class="heading-xs mt-8" data-reveal>
                @if ($event)
                    {{ $date }}@if ($event->location) · {{ $event->location }}@endif
                @else
                    Segera hadir
                @endif
            </p>
        </section>

        {{-- Timeline --}}
        <section class="timeline relative z-10 -mt-[50px] rounded-t-[50px] bg-beige px-5 pt-14 pb-0 md:p-0">
            <div class="md:absolute md:top-[2.1%] md:left-[7.5vw] md:w-[42vw]">
                <p class="heading-xs text-balance max-md:text-[1.625rem]">
                    {{ $event?->description ?: 'Centaurian Run adalah fun run keluarga besar Alfa Centauri. Siswa, guru, alumni, dan orang tua lari bersama: daftar online, check-in dengan barcode, dan lihat hasilmu langsung setelah finish.' }}
                </p>
                <div class="mt-8">@include('partials.btn', ['href' => '/daftar', 'label' => 'Daftar sekarang'])</div>
            </div>

            {{-- Illustration layer (desktop) --}}
            <svg class="pointer-events-none absolute inset-0 hidden size-full md:block" viewBox="0 0 1440 5256" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
                {{-- Stadium track --}}
                <g data-speed="0.4">
                    <rect x="330" y="470" width="760" height="460" rx="230" fill="var(--color-red)"/>
                    <rect x="352" y="492" width="716" height="416" rx="208" fill="none" stroke="#fff" stroke-width="3"/>
                    <rect x="374" y="514" width="672" height="372" rx="186" fill="none" stroke="#fff" stroke-width="3"/>
                    <rect x="396" y="536" width="628" height="328" rx="164" fill="var(--color-green)"/>
                    <rect x="560" y="620" width="300" height="160" rx="4" fill="none" stroke="#fff" stroke-width="3"/>
                    <path d="M710 620v160" stroke="#fff" stroke-width="3"/>
                    <circle cx="710" cy="700" r="34" fill="none" stroke="#fff" stroke-width="3"/>
                    <g fill="var(--color-charcoal)">
                        <rect x="600" y="886" width="11" height="11"/><rect x="611" y="897" width="11" height="11"/><rect x="600" y="908" width="11" height="11"/><rect x="611" y="919" width="11" height="11"/>
                    </g>
                </g>
                {{-- Sun --}}
                <g data-speed="-0.8">
                    <circle cx="1190" cy="470" r="70" fill="var(--color-yellow)"/>
                    <g stroke="var(--color-yellow)" stroke-width="10" stroke-linecap="round">
                        <path d="M1190 365v-25M1190 575v25M1085 470h-25M1295 470h25M1116 396l-18-18M1264 544l18 18M1116 544l-18 18M1264 396l18-18"/>
                    </g>
                </g>

                {{-- Route: dotted plan + green road drawn on scroll --}}
                @php($route = 'M560 930 C560 1100 400 1150 400 1400 C400 1700 1000 1650 1000 1950 C1000 2250 1050 2450 760 2600 C420 2780 230 2850 260 3100 C290 3400 1180 3400 1150 3650 C1120 3850 400 3800 360 4100 C330 4400 720 4500 720 5050')
                <path d="{{ $route }}" fill="none" stroke="var(--color-beige-500)" stroke-width="8" stroke-linecap="round" stroke-dasharray="1 22"/>
                <path d="{{ $route }}" fill="none" stroke="var(--color-green)" stroke-width="90" stroke-linecap="round" data-draw/>

                {{-- Stopwatch --}}
                <g data-speed="-0.6">
                    <rect x="1105" y="968" width="30" height="22" rx="5" fill="var(--color-charcoal)"/>
                    <circle cx="1120" cy="1060" r="68" fill="#fff" stroke="var(--color-charcoal)" stroke-width="7"/>
                    <path d="M1120 1060v-42M1120 1060l28 18" stroke="var(--color-blue)" stroke-width="7" stroke-linecap="round"/>
                    <circle cx="1120" cy="1060" r="8" fill="var(--color-charcoal)"/>
                </g>
                {{-- Bunting --}}
                <g data-speed="0.5">
                    <path d="M110 1730 Q300 1800 500 1720" fill="none" stroke="var(--color-charcoal)" stroke-width="4"/>
                    <path d="M150 1748l20 52 22-44z" fill="var(--color-blue)"/>
                    <path d="M230 1768l14 54 30-42z" fill="var(--color-yellow)"/>
                    <path d="M315 1770l10 54 34-38z" fill="var(--color-red)"/>
                    <path d="M400 1755l4 54 38-30z" fill="var(--color-pink)"/>
                </g>
                {{-- KM marker --}}
                <g data-speed="-0.5">
                    <rect x="212" y="2440" width="12" height="190" fill="var(--color-charcoal)"/>
                    <rect x="140" y="2370" width="156" height="96" rx="12" fill="var(--color-blue)"/>
                    <text x="218" y="2436" text-anchor="middle" font-size="54" font-weight="700" fill="#fff" font-family="Inter, sans-serif" letter-spacing="-3">5K</text>
                </g>
                {{-- Medal --}}
                <g data-speed="0.7">
                    <path d="M1195 2330l40 110 40-110z" fill="var(--color-pink)"/>
                    <path d="M1235 2440l40-110h40l-50 120z" fill="var(--color-red)"/>
                    <circle cx="1255" cy="2510" r="72" fill="var(--color-yellow)" stroke="var(--color-charcoal)" stroke-width="6"/>
                    <path d="m1255 2468 12 26 28 3-21 19 6 28-25-14-25 14 6-28-21-19 28-3z" fill="var(--color-charcoal)"/>
                </g>
                {{-- Confetti --}}
                <circle cx="760" cy="1250" r="16" fill="var(--color-red)" data-speed="-1.2"/>
                <path d="m1310 1780 30 52h-60z" fill="var(--color-blue)" data-speed="1"/>
                <path d="M640 2200q20-30 40 0t40 0 40 0" fill="none" stroke="var(--color-yellow)" stroke-width="10" stroke-linecap="round" data-speed="-1"/>
                <circle cx="150" cy="3380" r="22" fill="var(--color-pink)" data-speed="0.9"/>
                <path d="M1240 3180q20-30 40 0t40 0 40 0" fill="none" stroke="var(--color-red)" stroke-width="10" stroke-linecap="round" data-speed="-0.8"/>
                <circle cx="640" cy="3700" r="14" fill="var(--color-blue)" data-speed="1.2"/>
                <rect x="1320" y="4330" width="26" height="26" rx="4" transform="rotate(20 1333 4343)" fill="var(--color-yellow)" data-speed="-1"/>
                {{-- Clouds --}}
                <g fill="#fff">
                    <path d="M180 4470h240a40 40 0 0 0-50-56 60 60 0 0 0-110-10 46 46 0 0 0-80 66z" data-speed="-0.6"/>
                    <path d="M1020 4420h300a46 46 0 0 0-56-62 70 70 0 0 0-130-14 52 52 0 0 0-114 76z" data-speed="-1"/>
                    <path d="M1240 4740h150a26 26 0 0 0-32-36 38 38 0 0 0-70-6 30 30 0 0 0-48 42z" data-speed="-0.4"/>
                    <path d="M40 4770h110a22 22 0 0 0-26-30 32 32 0 0 0-58-4 24 24 0 0 0-26 34z" data-speed="-0.8"/>
                </g>
                {{-- Finish arch --}}
                <g>
                    <rect x="520" y="4570" width="16" height="480" fill="var(--color-charcoal)"/>
                    <rect x="904" y="4570" width="16" height="480" fill="var(--color-charcoal)"/>
                    <rect x="490" y="4530" width="460" height="86" rx="12" fill="var(--color-red)"/>
                    <text x="720" y="4592" text-anchor="middle" font-size="50" font-weight="700" fill="#fff" font-family="Inter, sans-serif" letter-spacing="-2">FINISH</text>
                </g>
                {{-- Hills (front is the callout green, so the seam disappears) --}}
                <path d="M0 4950C240 4850 480 4850 720 4930S1200 4860 1440 4900V5256H0Z" fill="var(--color-green-600)" data-speed="0.6"/>
                <path d="M0 5040C300 4960 560 5000 800 5050S1250 4980 1440 5020V5256H0Z" fill="var(--color-green-300)" data-speed="0.3"/>
                <path d="M0 5120C360 5060 700 5100 1000 5140S1300 5080 1440 5100V5257H0Z" fill="var(--color-green)"/>
            </svg>

            {{-- Cards --}}
            <div class="mt-14 flex flex-col gap-5 md:mt-0">
                @foreach ($cards as $card)
                    @php($cms = $c->get($card['id']))
                    <article id="{{ $card['id'] }}" class="card scroll-mt-28 px-5 py-6 md:absolute md:top-(--top) md:left-(--left) md:w-[29vw] md:max-w-[800px]" style="{{ $card['pos'] }}" data-reveal>
                        <h2 class="heading-sm">{{ $cms?->title ?? $card['title'] }}</h2>
                        <p class="mt-4 text-[clamp(1.0625rem,1rem+.3vw,1.26rem)] leading-[1.25] tracking-[-0.04em]">
                            {!! nl2br(e($cms?->body ?? $card['body'])) !!}
                        </p>

                        @if ($card['id'] === 'kategori' && $categories->isNotEmpty())
                            <ul class="mt-5 flex flex-wrap gap-2">
                                @foreach ($categories as $category)
                                    <li class="rounded-full bg-beige px-3 py-1 text-[.9375rem]">{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        @elseif ($card['id'] === 'paket' && $packages->isNotEmpty())
                            <ul class="mt-5 divide-y divide-beige-500">
                                @foreach ($packages as $package)
                                    <li class="flex justify-between py-2"><span>{{ $package->name }}</span><span>{{ $rupiah($package->price) }}</span></li>
                                @endforeach
                            </ul>
                        @elseif ($card['id'] === 'jadwal')
                            <dl class="mt-5 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1">
                                <dt class="opacity-75">Tanggal</dt><dd>{{ $date ?? 'Segera diumumkan' }}</dd>
                                @if ($time)<dt class="opacity-75">Start</dt><dd>{{ $time }} WIB</dd>@endif
                                @if ($event?->location)<dt class="opacity-75">Lokasi</dt><dd>{{ $event->location }}</dd>@endif
                            </dl>
                        @endif

                        <div class="mt-6">@include('partials.btn', ['href' => $card['href'], 'label' => $card['button'], 'bg' => $card['bg'], 'primary' => true])</div>
                    </article>
                @endforeach
            </div>

            {{-- Hills (mobile) --}}
            <svg class="-mx-5 mt-14 block w-[calc(100%+2.5rem)] md:hidden" viewBox="0 4850 1440 407" aria-hidden="true">
                <path d="M0 4950C240 4850 480 4850 720 4930S1200 4860 1440 4900V5257H0Z" fill="var(--color-green-600)"/>
                <path d="M0 5040C300 4960 560 5000 800 5050S1250 4980 1440 5020V5257H0Z" fill="var(--color-green-300)"/>
                <path d="M0 5120C360 5060 700 5100 1000 5140S1300 5080 1440 5100V5257H0Z" fill="var(--color-green)"/>
            </svg>
        </section>
    </div>

    {{-- Callout --}}
    <section class="relative z-10 -mt-px rounded-b-[50px] bg-green px-5 pt-[clamp(80px,10vw,160px)] pb-[clamp(60px,6vw,100px)] text-center">
        <h2 class="heading-2xl" data-lines>
            @foreach (preg_split('/\R/', $ctaTitle) as $line)
                <span class="block overflow-hidden pb-[.08em]"><span class="block">{{ $line }}</span></span>
            @endforeach
        </h2>
        <p class="heading-xs mx-auto mt-14 max-w-[35rem] text-balance" data-reveal>
            {{ $c->get('cta')?->body ?? 'Pilih kategori, pilih paket, dan amankan nomor pesertamu. Pendaftaran cuma butuh beberapa menit.' }}
        </p>
        <div class="mt-8" data-reveal>@include('partials.btn', ['href' => '/daftar', 'label' => 'Daftar sekarang'])</div>

        <ul class="mx-auto mt-[clamp(80px,12vw,220px)] grid max-w-[1200px] gap-12 text-[1.125rem] tracking-[-0.04em] md:grid-cols-3">
            <li class="flex flex-col items-center gap-5" data-reveal>
                <svg width="96" height="72" viewBox="0 0 96 72" aria-hidden="true"><circle cx="24" cy="24" r="12" fill="var(--color-yellow)"/><circle cx="48" cy="18" r="14" fill="var(--color-red)"/><circle cx="72" cy="24" r="12" fill="var(--color-blue)"/><path d="M6 66a18 18 0 0 1 36 0M28 66a20 20 0 0 1 40 0M54 66a18 18 0 0 1 36 0" fill="var(--color-charcoal)"/></svg>
                {{ $categories->count() }} kategori peserta
            </li>
            <li class="flex flex-col items-center gap-5" data-reveal>
                <svg width="96" height="72" viewBox="0 0 96 72" aria-hidden="true"><path d="M20 8h34l34 34-30 26L20 34z" fill="var(--color-pink)" stroke="var(--color-charcoal)" stroke-width="4" stroke-linejoin="round"/><circle cx="36" cy="22" r="6" fill="var(--color-charcoal)"/></svg>
                {{ $minPrice === null ? 'Paket segera diumumkan' : ($minPrice === 0 ? 'Mulai gratis' : 'Mulai ' . $rupiah($minPrice)) }}
            </li>
            <li class="flex flex-col items-center gap-5" data-reveal>
                <svg width="96" height="72" viewBox="0 0 96 72" aria-hidden="true"><rect x="16" y="10" width="64" height="58" rx="8" fill="#fff" stroke="var(--color-charcoal)" stroke-width="4"/><path d="M16 26h64" stroke="var(--color-charcoal)" stroke-width="4"/><path d="M32 4v12M64 4v12" stroke="var(--color-charcoal)" stroke-width="4" stroke-linecap="round"/><rect x="40" y="36" width="16" height="16" rx="3" fill="var(--color-red)"/></svg>
                {{ $date ?? 'Tanggal segera diumumkan' }}
            </li>
        </ul>
    </section>
</main>

<footer class="py-8 text-center text-[.9375rem] opacity-75">© {{ now()->year }} Alcent Centaurian Run</footer>

</body>
</html>
