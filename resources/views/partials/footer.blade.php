{{-- Site footer: brand, navigation, contact and social columns, then a legal bar. Pass 'class' for spacing.
     Name, contact and social links come from Admin → Pengaturan; empty ones are left out. --}}
@php
    $s = app(\App\Services\SettingService::class)->getAllFlat();
    $name = ($s['app_name'] ?? '') ?: 'Centaurian Run';
    $email = $s['contact_email'] ?? '';
    $phone = $s['contact_phone'] ?? '';
    $contact = array_filter([
        $email ? ['label' => $email, 'href' => 'mailto:' . $email] : null,
        $phone ? ['label' => $phone, 'href' => 'tel:' . preg_replace('/[^\d+]/', '', $phone)] : null,
    ]);
    // Only http(s) links, so a stray "javascript:" value can't end up in a public href
    $social = array_filter(
        ['WhatsApp' => $s['whatsapp_url'] ?? '', 'Instagram' => $s['instagram_url'] ?? '', 'Facebook' => $s['facebook_url'] ?? ''],
        fn ($url) => preg_match('#^https?://#i', $url)
    );
    $nav = ['Tentang' => '/#tentang', 'Kategori' => '/#kategori', 'Paket' => '/#paket', 'Jadwal' => '/#jadwal', 'Daftar' => '/daftar', 'BIB Checking' => '/cek-status', 'Hasil Lomba' => '/hasil'];
@endphp
<footer class="body-md {{ $class ?? '' }}">
    <div class="grid gap-x-gutter gap-y-8 sm:grid-cols-2 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div>
            <a href="/" class="inline-flex items-center gap-3 py-2">
                <img src="/images/logo.png" alt="" width="824" height="490" class="h-[30px] w-auto">
                <span class="heading-xs">{{ $name }}</span>
            </a>
            <p class="mt-3 max-w-80">Lari bersama keluarga besar Yayasan Taqwa Cerdas Kreatif.</p>
        </div>

        <nav aria-label="Footer">
            <h2 class="font-bold">Jelajahi</h2>
            <ul class="mt-2">
                @foreach ($nav as $label => $href)
                    <li><a class="c-link inline-block py-3" href="{{ $href }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        @if ($contact)
            <div>
                <h2 class="font-bold">Kontak</h2>
                <ul class="mt-2">
                    @foreach ($contact as $item)
                        <li><a class="c-link inline-block py-3" href="{{ $item['href'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($social)
            <div>
                <h2 class="font-bold">Ikuti kami</h2>
                <ul class="mt-2">
                    @foreach ($social as $label => $href)
                        <li><a class="c-link inline-block py-3" href="{{ $href }}" target="_blank" rel="noopener">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="mt-10 flex flex-col gap-x-gutter gap-y-1 border-t border-current pt-4 sm:flex-row sm:items-center sm:justify-between">
        <p>© {{ now()->year }} Alcent Centaurian Run · Yayasan Taqwa Cerdas Kreatif. Hak cipta dilindungi.</p>
        <a class="c-link inline-block py-3" href="/login">Login panitia</a>
    </div>
</footer>
