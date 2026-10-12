@php
    $c = $contents;
    $rupiah = fn (int $p) => $p === 0 ? 'Gratis' : 'Rp' . number_format($p, 0, ',', '.');
    $date = $event?->event_date?->locale('id')->translatedFormat('l, j F Y');
    $time = $event?->start_time?->format('H:i');
    $minPrice = $packages->min('price');
    $ctaTitle = $c->get('cta')?->title ?? "Siap lari\nbareng?";
    // Hero title breaks after the first word: "Centaurian" / "Run 2026"
    $name = $event?->name ?? 'Centaurian Run ' . now()->year;
    [$heroTop, $heroBottom] = explode(' ', $name, 2) + [1 => null];

    // Shrub silhouette: overlapping arcs (chord 1.6r, so each bump is 0.4r tall) along y, closed at the timeline bottom
    $bush = function (int $y, array $radii) {
        $d = "M-20 {$y}";
        for ($x = -20, $i = 0; $x < 1460; $i++) {
            $r = $radii[$i % count($radii)];
            $d .= ' a' . $r . ' ' . $r . ' 0 0 1 ' . (1.6 * $r) . ' 0';
            $x += 1.6 * $r;
        }
        return $d . ' V5257 H-20Z';
    };
    // [x, size, petal colour] along the front shrub; the gap at 600-840 keeps the finish arch clear
    $flowers = [[20, 52, 'var(--color-pink)'], [150, 64, 'var(--color-red)'], [290, 46, '#fff'], [410, 58, 'var(--color-pink)'],
        [950, 56, 'var(--color-red)'], [1070, 46, 'var(--color-blue)'], [1180, 64, 'var(--color-pink)'], [1310, 50, '#fff'], [1395, 44, 'var(--color-red)']];

    $cards = [
        [
            'id' => 'kategori', 'bg' => 'var(--color-red)', 'href' => '#paket', 'button' => 'Daftar',
            'pos' => '--top:23%; --left:56.9vw',
            'title' => 'Semua bisa lari.',
            'body' => 'Siswa, guru, alumni, orang tua, sampai tamu dari luar. Centaurian Run terbuka untuk semua kalangan, pilih kategorimu saat mendaftar.',
        ],
        [
            'id' => 'paket', 'bg' => 'var(--color-yellow)', 'href' => '#paket', 'button' => 'Pilih paket',
            'pos' => '--top:36.3%; --left:7.6vw',
            'title' => 'Pilih paketmu.',
            'body' => 'Mulai dari gratis. Untuk paket berbayar, cukup transfer, unggah bukti pembayaran, dan tunggu verifikasi panitia.',
        ],
        [
            'id' => 'jadwal', 'bg' => 'var(--color-blue)', 'href' => '#jadwal', 'button' => 'Cek status',
            'pos' => '--top:54.1%; --left:32.6vw',
            'title' => 'Catat tanggalnya.',
            'body' => 'Datang lebih awal dan pemanasan bareng. Cek status pendaftaranmu kapan saja dengan nomor registrasi.',
        ],
        [
            'id' => 'hasil', 'bg' => 'var(--color-pink)', 'href' => '#hasil', 'button' => 'Hasil lomba',
            'pos' => '--top:74%; --left:56.9vw',
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
    <title>{{ $name }}</title>
    <meta name="description" content="{{ Str::limit($event?->description ?? 'Fun run keluarga besar Alfa Centauri.', 155) }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">

{{-- Nav --}}
<header class="nav fixed inset-x-4 top-4 z-50 mx-auto flex max-w-[1405px] gap-5 xl:inset-x-[7.5vw]">
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
        <details class="relative ml-2">
            <summary class="grid size-11 md:size-10 cursor-pointer list-none place-items-center rounded-full bg-green" aria-label="Menu">
                <svg width="22" height="22" viewBox="0 0 22 22" aria-hidden="true"><path d="M5 8h12M5 14h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </summary>
            <div class="absolute top-14 right-0 flex w-56 flex-col rounded-sm bg-white p-2 shadow-lg">
                @foreach ($cards as $card)
                    <a href="#{{ $card['id'] }}" class="nav-link md:hidden"><span>{{ ucfirst($card['id']) }}</span></a>
                @endforeach
                <a href="#paket" class="nav-link"><span>Daftar</span></a>
                <a href="#jadwal" class="nav-link"><span>Cek status</span></a>
                <a href="#hasil" class="nav-link"><span>Hasil lomba</span></a>
            </div>
        </details>
    </div>
    <a href="#paket" class="nav-cta relative hidden h-16 items-center gap-4 rounded-sm bg-white pr-2.5 pl-5 text-[1.0625rem] md:flex">
        <span>Daftar sekarang</span>
        <span class="grid size-10 place-items-center rounded-full bg-green transition-transform duration-300">
            <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="m8 14 4-4-4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
    </a>
</header>

<main>
    <div class="relative">
        {{-- Hero: sticks while the timeline slides over it --}}
        <section class="hero sticky top-0 flex h-[calc(100svh+50px)] flex-col items-center overflow-hidden bg-green px-4 pt-[clamp(130px,24vh,230px)] text-center md:pt-0">
            {{-- Title bottom sits at 52.1% of the viewport height, as on the reference --}}
            <div class="flex flex-col justify-end md:h-[52.1svh]">
                <h1 class="heading-2xl" data-reveal>{{ $heroTop }}@if ($heroBottom)<br>{{ $heroBottom }}@endif</h1>
            </div>
            <p class="heading-xs mt-12" data-reveal>
                @if ($event)
                    {{ $date }}@if ($event->location) · {{ $event->location }}@endif
                @else
                    Segera hadir
                @endif
            </p>
        </section>

        {{-- Timeline --}}
        <section class="timeline relative z-10 -mt-[50px] rounded-t-[50px] bg-beige px-5 pt-14 pb-0 md:p-0">
            <svg class="absolute size-0" aria-hidden="true">
                <symbol id="cloud" viewBox="0 0 200 60"><path d="M14 60C-6 60-2 34 22 36 24 14 54 6 72 24 80 2 124-2 134 26 148 12 178 18 178 40 196 38 206 60 186 60Z" fill="#fff"/></symbol>
                <symbol id="sprig" viewBox="0 0 34 40"><path d="M17 40V14" stroke="var(--color-green-300)" stroke-width="3" stroke-linecap="round"/><path d="M17 24C8 22 4 14 6 8c8 2 12 8 11 16zM17 18c8-2 12-9 10-15-8 2-11 7-10 15z" fill="var(--color-green-300)"/></symbol>
                <symbol id="flower" viewBox="-20 -24 40 84">
                    <path d="M0 60V2" stroke="var(--color-green-600)" stroke-width="4" stroke-linecap="round"/>
                    <path d="M0 40c-12-2-16-12-14-18 8 2 14 8 14 18z" fill="var(--color-green-600)"/>
                    <g fill="currentColor"><circle cy="-13" r="7"/><circle cx="8.6" cy="-6.8" r="7"/><circle cx="5.3" cy="3.3" r="7"/><circle cx="-5.3" cy="3.3" r="7"/><circle cx="-8.6" cy="-6.8" r="7"/></g>
                    <circle cy="-4" r="5" fill="var(--color-yellow)" stroke="var(--color-charcoal)" stroke-width="1.5"/>
                </symbol>
            </svg>
            <div class="relative z-20 md:absolute md:top-[2.1%] md:left-[7.5vw] md:w-[42vw]">
                <p class="heading-xs text-balance max-md:text-[1.625rem]">
                    {{ $event?->description ?: 'Centaurian Run adalah fun run keluarga besar Alfa Centauri. Siswa, guru, alumni, dan orang tua lari bersama: daftar online, check-in dengan barcode, dan lihat hasilmu langsung setelah finish.' }}
                </p>
                <div class="mt-8">@include('partials.btn', ['href' => '#paket', 'label' => 'Daftar sekarang'])</div>
            </div>

            {{-- Runners (Open Doodles, recoloured): overlap the hero bottom, then trail the scroll down to where the route starts --}}
            <div class="pointer-events-none absolute top-[-32svh] md:top-[-38svh] inset-x-0 mx-auto z-10 block h-[52vw] w-[88vw] md:h-[30vw] md:w-[50vw] max-w-[750px]" aria-hidden="true" data-track>
                <svg class="absolute inset-0 size-full overflow-visible" viewBox="0 0 720 432">
                    <use href="#cloud" x="575" y="-10" width="150" height="45"/>
                    <use href="#cloud" x="-60" y="360" width="140" height="42"/>
                    <path d="M590 80q12-18 24 0t24 0t24 0" fill="none" stroke="var(--color-yellow)" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="690" cy="150" r="8" fill="var(--color-blue)"/><ellipse cx="687" cy="147" rx="2" ry="3" fill="#fff"/>
                    <circle cx="100" cy="30" r="9" fill="var(--color-red)"/><ellipse cx="97" cy="26" rx="2.5" ry="4" fill="#fff"/>
                    <circle cx="20" cy="190" r="5" fill="var(--color-charcoal)"/>
                    <path d="M640 250q20 10 30 32" fill="none" stroke="var(--color-charcoal)" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                {{-- Flips live on wrappers: GSAP animates the <img> transform and would drop a CSS flip on the same element --}}
                <img src="/images/doodles/groovy.svg" alt="" class="absolute top-[12%] left-0 w-[32%]">
                <div class="absolute top-0 left-[24%] w-[34%] -scale-x-100"><img src="/images/doodles/jumping.svg" alt="" class="w-full"></div>
                <div class="absolute top-[10%] left-[58%] w-[34%] -scale-x-100"><img src="/images/doodles/rolling.svg" alt="" class="w-full"></div>
                <img src="/images/doodles/running.svg" alt="" class="absolute top-[44%] left-[22%] w-[50%]">
            </div>

            {{-- Illustration layer (desktop). Coordinates are px at 1440 wide; cards sit at
                 kategori 819-1332 × 1209-1669, paket 109-622 × 1908-2399, jadwal 469-982 × 2843-3290, hasil 819-1332 × 3889-4229 --}}
            <svg class="pointer-events-none absolute inset-0 hidden size-full md:block" viewBox="0 0 1440 5256" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
                {{-- Sun --}}
                <g data-speed="-0.8"><g transform="translate(-960 180)">
                    <circle cx="1190" cy="470" r="70" fill="var(--color-yellow)"/>
                    <g stroke="var(--color-yellow)" stroke-width="10" stroke-linecap="round">
                        <path d="M1190 365v-25M1190 575v25M1085 470h-25M1295 470h25M1116 396l-18-18M1264 544l18 18M1116 544l-18 18M1264 396l18-18"/>
                    </g>
                </g></g>

                {{-- Clouds tucked behind card corners --}}
                <use href="#cloud" x="735" y="1150" width="200" height="60" data-speed="-0.3"/>
                <use href="#cloud" x="520" y="2355" width="180" height="54" data-speed="-0.3"/>
                <use href="#cloud" x="390" y="3245" width="160" height="48" data-speed="-0.3"/>
                <use href="#cloud" x="1235" y="3840" width="200" height="60" data-speed="-0.3"/>

                {{-- Route: dotted plan + road shadow + green road drawn on scroll --}}
                @php($route = 'M1000 860 C1000 1100 380 1100 380 1450 C380 1750 1050 1700 1050 2000 C1050 2300 1180 2450 1180 2750 C1180 3000 1250 3150 1200 3350 C1150 3600 300 3500 300 3800 C300 4150 300 4300 500 4450 C700 4600 720 4700 720 5050')
                <path d="{{ $route }}" fill="none" stroke="var(--color-beige-500)" stroke-width="8" stroke-linecap="round" stroke-dasharray="1 22"/>
                <path d="{{ $route }}" fill="none" stroke="var(--color-charcoal)" stroke-width="226" stroke-linecap="round" opacity="0.07" transform="translate(0 12)"/>
                <path d="{{ $route }}" fill="none" stroke="var(--color-green)" stroke-width="220" stroke-linecap="round" data-draw/>

                {{-- Kategori (red): bunting + confetti thrown toward the card --}}
                <g data-speed="0.5">
                    <path d="M110 1730 Q300 1800 500 1720" fill="none" stroke="var(--color-charcoal)" stroke-width="4"/>
                    <path d="M150 1748l20 52 22-44z" fill="var(--color-blue)"/>
                    <path d="M230 1768l14 54 30-42z" fill="var(--color-yellow)"/>
                    <path d="M315 1770l10 54 34-38z" fill="var(--color-red)"/>
                    <path d="M400 1755l4 54 38-30z" fill="var(--color-pink)"/>
                    <path d="M520 1705c70-30 150-120 250-130" fill="none" stroke="var(--color-charcoal)" stroke-width="3" stroke-linecap="round" stroke-dasharray="2 12"/>
                </g>
                <g data-speed="-1.2"><g data-float>
                    <circle cx="760" cy="1250" r="18" fill="var(--color-red)"/>
                    <ellipse cx="753" cy="1243" rx="5" ry="7" transform="rotate(35 753 1243)" fill="#fff"/>
                </g></g>
                <path d="M1350 1170q15-22 30 0t30 0" fill="none" stroke="var(--color-yellow)" stroke-width="9" stroke-linecap="round" data-speed="-0.9"/>
                <g data-speed="0.8"><g data-float>
                    <circle cx="1378" cy="1520" r="15" fill="var(--color-blue)"/>
                    <ellipse cx="1372" cy="1514" rx="4" ry="6" transform="rotate(35 1372 1514)" fill="#fff"/>
                </g></g>
                <path d="m1310 1780 30 52h-60z" fill="var(--color-blue)" data-speed="1"/>
                <circle cx="790" cy="1625" r="6" fill="var(--color-charcoal)" data-speed="-0.6"/>

                {{-- Paket (yellow): price tag on a string + flying coins --}}
                <g data-speed="-0.5"><g data-float>
                    <g transform="translate(680 1925) rotate(14) scale(2.4)">
                        <path d="M36 22c-22-16-14-40 8-36" fill="none" stroke="var(--color-charcoal)" stroke-width="1.2" stroke-linecap="round"/>
                        <path d="M20 8h34l34 34-30 26L20 34z" fill="var(--color-pink)" stroke="var(--color-charcoal)" stroke-width="2" stroke-linejoin="round"/>
                        <circle cx="36" cy="22" r="4.5" fill="var(--color-beige)" stroke="var(--color-charcoal)" stroke-width="2"/>
                        <ellipse cx="62" cy="40" rx="3" ry="8" transform="rotate(-45 62 40)" fill="#fff" opacity=".8"/>
                    </g>
                </g></g>
                <path d="M738 2205q-46-62 0-120" fill="none" stroke="var(--color-charcoal)" stroke-width="3" stroke-linecap="round" stroke-dasharray="2 12" data-speed="0.4"/>
                @foreach ([[820, 2140, 36, 0.6], [760, 2240, 30, 0.9], [885, 2300, 40, 0.3]] as [$cx, $cy, $r, $speed])
                    <g data-speed="{{ $speed }}"><g data-float>
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="var(--color-yellow)" stroke="var(--color-charcoal)" stroke-width="5"/>
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r - 10 }}" fill="none" stroke="var(--color-charcoal)" stroke-width="2.5" opacity=".35"/>
                        <text x="{{ $cx }}" y="{{ $cy + $r * .28 }}" text-anchor="middle" font-size="{{ $r * .75 }}" font-weight="700" fill="var(--color-charcoal)" font-family="Inter, sans-serif" letter-spacing="-1">Rp</text>
                        <ellipse cx="{{ $cx - $r * .45 }}" cy="{{ $cy - $r * .45 }}" rx="{{ $r * .14 }}" ry="{{ $r * .24 }}" transform="rotate(40 {{ $cx - $r * .45 }} {{ $cy - $r * .45 }})" fill="#fff"/>
                    </g></g>
                @endforeach
                {{-- KM marker --}}
                <g data-speed="-0.5"><g transform="translate(0 110)">
                    <rect x="212" y="2440" width="12" height="190" fill="var(--color-charcoal)"/>
                    <rect x="140" y="2370" width="156" height="96" rx="12" fill="var(--color-blue)"/>
                    <text x="218" y="2436" text-anchor="middle" font-size="54" font-weight="700" fill="#fff" font-family="Inter, sans-serif" letter-spacing="-3">5K</text>
                    <path d="M310 2400q14 20 0 40M330 2390q22 30 0 60" fill="none" stroke="var(--color-charcoal)" stroke-width="3" stroke-linecap="round"/>
                </g></g>

                {{-- Jadwal (blue): stopwatch with motion lines + calendar page --}}
                <g data-speed="-0.6"><g data-float><g transform="translate(-870 1900)">
                    <rect x="1105" y="968" width="30" height="22" rx="5" fill="var(--color-charcoal)"/>
                    <circle cx="1120" cy="1060" r="68" fill="#fff" stroke="var(--color-charcoal)" stroke-width="7"/>
                    <path d="M1120 1060v-42M1120 1060l28 18" stroke="var(--color-blue)" stroke-width="7" stroke-linecap="round"/>
                    <circle cx="1120" cy="1060" r="8" fill="var(--color-charcoal)"/>
                    <ellipse cx="1088" cy="1028" rx="7" ry="15" transform="rotate(40 1088 1028)" fill="var(--color-beige-500)"/>
                    <path d="M1032 1010q-22 22-16 52M1010 1030q-14 16-10 36" fill="none" stroke="var(--color-charcoal)" stroke-width="3" stroke-linecap="round"/>
                </g></g></g>
                <g data-speed="0.4"><g transform="rotate(-8 165 3170)">
                    <rect x="100" y="3110" width="130" height="120" rx="10" fill="#fff" stroke="var(--color-charcoal)" stroke-width="5"/>
                    <path d="M100 3142v-22a10 10 0 0 1 10-10h110a10 10 0 0 1 10 10v22z" fill="var(--color-red)" stroke="var(--color-charcoal)" stroke-width="5" stroke-linejoin="round"/>
                    <path d="M130 3098v22M200 3098v22" stroke="var(--color-charcoal)" stroke-width="6" stroke-linecap="round"/>
                    <g fill="var(--color-charcoal)">
                        @foreach ([[125, 3162], [155, 3162], [185, 3162], [125, 3192], [155, 3192]] as [$gx, $gy])<rect x="{{ $gx }}" y="{{ $gy }}" width="12" height="12" rx="2"/>@endforeach
                    </g>
                    <circle cx="191" cy="3198" r="16" fill="none" stroke="var(--color-blue)" stroke-width="4"/>
                </g></g>
                <g data-speed="0.9"><g data-float>
                    <circle cx="1010" cy="2820" r="14" fill="var(--color-blue)"/>
                    <ellipse cx="1005" cy="2814" rx="4" ry="6" transform="rotate(35 1005 2814)" fill="#fff"/>
                </g></g>
                <path d="M1340 2850q15-22 30 0t30 0" fill="none" stroke="var(--color-red)" stroke-width="9" stroke-linecap="round" data-speed="-0.8"/>
                <g data-speed="0.9"><g data-float>
                    <circle cx="150" cy="3400" r="22" fill="var(--color-pink)"/>
                    <ellipse cx="142" cy="3391" rx="5" ry="8" transform="rotate(35 142 3391)" fill="#fff"/>
                </g></g>

                {{-- Hasil (pink): trophy with sparkles + medal swinging under the card --}}
                <g data-speed="-0.4"><g data-float>
                    <path d="M520 3925l-20-14M515 3965h-24M720 3925l20-14M725 3965h24" stroke="var(--color-charcoal)" stroke-width="4" stroke-linecap="round"/>
                    <path d="M560 3955h-22a22 22 0 0 0 22 40M680 3955h22a22 22 0 0 1-22 40" fill="none" stroke="var(--color-charcoal)" stroke-width="6"/>
                    <path d="M560 3940h120v40a60 60 0 0 1-120 0z" fill="var(--color-yellow)" stroke="var(--color-charcoal)" stroke-width="6" stroke-linejoin="round"/>
                    <path d="m620 3958 7 15 16 2-12 11 3 16-14-8-14 8 3-16-12-11 16-2z" fill="var(--color-charcoal)"/>
                    <ellipse cx="582" cy="3975" rx="7" ry="17" fill="#fff"/>
                    <rect x="610" y="4038" width="20" height="32" fill="var(--color-charcoal)"/>
                    <rect x="580" y="4068" width="80" height="26" rx="4" fill="var(--color-red)" stroke="var(--color-charcoal)" stroke-width="6"/>
                </g></g>
                <g data-speed="0.3"><g data-float><g transform="translate(-155 1820)">
                    <path d="M1195 2330l40 110 40-110z" fill="var(--color-pink)"/>
                    <path d="M1235 2440l40-110h40l-50 120z" fill="var(--color-red)"/>
                    <circle cx="1255" cy="2510" r="72" fill="var(--color-yellow)" stroke="var(--color-charcoal)" stroke-width="6"/>
                    <path d="m1255 2468 12 26 28 3-21 19 6 28-25-14-25 14 6-28-21-19 28-3z" fill="var(--color-charcoal)"/>
                    <ellipse cx="1218" cy="2474" rx="8" ry="16" transform="rotate(40 1218 2474)" fill="#fff"/>
                    <path d="M1350 2470q34 34 20 80M1372 2456q44 44 26 104" fill="none" stroke="var(--color-charcoal)" stroke-width="3" stroke-linecap="round"/>
                </g></g></g>
                <g data-speed="1.2"><g data-float>
                    <circle cx="640" cy="3700" r="14" fill="var(--color-blue)"/>
                    <ellipse cx="635" cy="3694" rx="4" ry="6" transform="rotate(35 635 3694)" fill="#fff"/>
                </g></g>
                <rect x="1320" y="4280" width="26" height="26" rx="4" transform="rotate(20 1333 4293)" fill="var(--color-yellow)" data-speed="-1"/>

                {{-- Finish: clouds + arch --}}
                <use href="#cloud" x="170" y="4400" width="260" height="78" data-speed="-0.6"/>
                <use href="#cloud" x="1200" y="4440" width="240" height="72" data-speed="-1"/>
                <use href="#cloud" x="1240" y="4690" width="150" height="45" data-speed="-0.4"/>
                <use href="#cloud" x="30" y="4730" width="120" height="36" data-speed="-0.8"/>
                <g>
                    <rect x="520" y="4570" width="16" height="480" fill="var(--color-charcoal)"/>
                    <rect x="904" y="4570" width="16" height="480" fill="var(--color-charcoal)"/>
                    <rect x="490" y="4530" width="460" height="86" rx="12" fill="var(--color-red)"/>
                    <text x="720" y="4592" text-anchor="middle" font-size="50" font-weight="700" fill="#fff" font-family="Inter, sans-serif" letter-spacing="-2">FINISH</text>
                </g>

                {{-- Shrubs (front is the callout green, so the seam disappears) --}}
                <path d="{{ $bush(4975, [70, 95, 60, 110, 80, 65, 100]) }}" fill="var(--color-green-600)" data-speed="0.6"/>
                <g data-speed="0.3">
                    <path d="{{ $bush(5060, [50, 72, 44, 86, 60, 55]) }}" fill="var(--color-green-300)"/>
                    @foreach ([90, 380, 1010, 1290] as $sx)<use href="#sprig" x="{{ $sx }}" y="4990" width="51" height="60"/>@endforeach
                </g>
                <path d="{{ $bush(5140, [40, 56, 34, 66, 48, 38, 60]) }}" fill="var(--color-green)"/>
                @foreach ($flowers as [$fx, $fs, $fc])
                    <use href="#flower" x="{{ $fx }}" y="{{ 5146 - $fs * 2.1 }}" width="{{ $fs }}" height="{{ $fs * 2.1 }}" style="color: {{ $fc }}"/>
                @endforeach
            </svg>

            {{-- Desktop distributed runners along the route --}}
            <div class="pointer-events-none absolute top-[24%] left-[22vw] z-10 hidden md:block w-[18vw] max-w-[260px]" data-character aria-hidden="true">
                <img src="/images/doodles/groovy.svg" alt="" class="w-full drop-shadow-sm">
            </div>
            <div class="pointer-events-none absolute top-[37%] left-[65vw] z-10 hidden md:block w-[19vw] max-w-[270px] -scale-x-100" data-character aria-hidden="true">
                <img src="/images/doodles/rolling.svg" alt="" class="w-full drop-shadow-sm">
            </div>
            <div class="pointer-events-none absolute top-[52.5%] left-[72vw] z-10 hidden md:block w-[19vw] max-w-[270px]" data-character aria-hidden="true">
                <img src="/images/doodles/jumping.svg" alt="" class="w-full drop-shadow-sm">
            </div>
            <div class="pointer-events-none absolute top-[83%] left-[26vw] z-10 hidden md:block w-[24vw] max-w-[340px]" data-character aria-hidden="true">
                <img src="/images/doodles/running.svg" alt="" class="w-full drop-shadow-sm">
            </div>

            {{-- Cards --}}
            <div class="mt-14 flex flex-col gap-5 md:mt-0">
                @foreach ($cards as $card)
                    @php($cms = $c->get($card['id']))
                    <article id="{{ $card['id'] }}" class="card scroll-mt-28 px-5 py-6 md:absolute md:top-(--top) md:left-(--left) md:w-[35.6vw] md:p-[3.125rem]" style="{{ $card['pos'] }}" data-reveal>
                        <h2 class="heading-sm">{{ $cms?->title ?? $card['title'] }}</h2>
                        <p class="mt-4 text-[1.1875rem] leading-[1.25] tracking-[-0.04em]">
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

                    {{-- Mobile runner spots between stacked cards --}}
                    @if ($card['id'] === 'kategori')
                        <div class="flex justify-center md:hidden my-2" data-character aria-hidden="true">
                            <img src="/images/doodles/groovy.svg" alt="" class="w-36 drop-shadow-sm">
                        </div>
                    @elseif ($card['id'] === 'paket')
                        <div class="flex justify-center md:hidden my-2" data-character aria-hidden="true">
                            <img src="/images/doodles/rolling.svg" alt="" class="w-36 drop-shadow-sm -scale-x-100">
                        </div>
                    @elseif ($card['id'] === 'jadwal')
                        <div class="flex justify-center md:hidden my-2" data-character aria-hidden="true">
                            <img src="/images/doodles/jumping.svg" alt="" class="w-36 drop-shadow-sm">
                        </div>
                    @elseif ($card['id'] === 'hasil')
                        <div class="flex justify-center md:hidden my-2" data-character aria-hidden="true">
                            <img src="/images/doodles/running.svg" alt="" class="w-44 drop-shadow-sm">
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Shrubs (mobile) --}}
            <svg class="-mx-5 mt-14 block w-[calc(100%+2.5rem)] md:hidden" viewBox="0 4880 1440 377" aria-hidden="true">
                <path d="{{ $bush(4975, [70, 95, 60, 110, 80, 65, 100]) }}" fill="var(--color-green-600)"/>
                <path d="{{ $bush(5060, [50, 72, 44, 86, 60, 55]) }}" fill="var(--color-green-300)"/>
                <path d="{{ $bush(5140, [40, 56, 34, 66, 48, 38, 60]) }}" fill="var(--color-green)"/>
                @foreach ($flowers as [$fx, $fs, $fc])
                    <use href="#flower" x="{{ $fx }}" y="{{ 5146 - $fs * 2.1 }}" width="{{ $fs }}" height="{{ $fs * 2.1 }}" style="color: {{ $fc }}"/>
                @endforeach
            </svg>
        </section>
    </div>

    {{-- Callout --}}
    <section class="callout relative z-10 -mt-px rounded-b-[50px] bg-green px-5 pt-32 md:pt-40 pb-[clamp(60px,6vw,100px)] text-center">
        {{-- Pop-up runner on CTA hover (inspired by MindMarket pop_up_girl) --}}
        <div class="pointer-events-none absolute top-[-100vh] bottom-0 left-0 w-[35vw] max-w-[440px] max-md:hidden z-20">
            <div class="sticky top-0 flex h-screen w-full items-end overflow-hidden">
                <div id="callout-popup-runner" class="w-full ml-[4vw]">
                    <img src="/images/doodles/jumping.svg" alt="" class="w-full drop-shadow-2xl -scale-x-100">
                </div>
            </div>
        </div>

        <h2 class="heading-2xl" data-lines>
            @foreach (preg_split('/\R/', $ctaTitle) as $line)
                <span class="block overflow-hidden pb-[.08em]"><span class="block">{{ $line }}</span></span>
            @endforeach
        </h2>
        <p class="heading-xs mx-auto mt-20 max-w-[35rem] text-balance" data-reveal>
            {{ $c->get('cta')?->body ?? 'Pilih kategori, pilih paket, dan amankan nomor pesertamu. Pendaftaran cuma butuh beberapa menit.' }}
        </p>
        <div class="mt-8" data-reveal>@include('partials.btn', ['href' => '#paket', 'label' => 'Daftar sekarang', 'attributes' => 'data-callout-action'])</div>

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
