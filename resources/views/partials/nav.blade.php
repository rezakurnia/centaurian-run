{{-- Desktop + mobile menus. $base is '' on the landing page and '/' on every other page, so the section links resolve to /#kategori. --}}
@php
    $base = $base ?? '';
    // Section anchors live on the landing page; "Rute" and "Tata Tertib" only exist there.
    // One list feeds both bars: label => href
    $links = ['Tentang' => $base . '#tentang', 'Kategori' => $base . '#kategori', 'Paket' => $base . '#paket', 'Jadwal' => $base . '#jadwal'];

    if ($base === '') {
        $links['Rute'] = '#rute';
        $links['Tata tertib'] = '#tata-tertib';
    }

    $links += ['Cek BIB' => '/cek-status', 'Hasil lomba' => '/hasil'];
    $current = fn (string $href) => $href === '/' . request()->path() ? 'page' : null;
    // Logo (shared by the desktop and mobile bars): 36x30 mark + wordmark
    $logo = '<span class="c-logo_mark"><img src="/images/logo.png" alt="" width="824" height="490"></span><span class="c-logo_text">Centaurian Run</span>';
@endphp

{{-- Inline so the nav and hero reveal even if the JS bundle fails to load or throws --}}
<script>requestAnimationFrame(() => document.documentElement.classList.add('is-loaded'))</script>
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-200 focus:rounded-sm focus:bg-white focus:p-3">Lewati ke konten</a>

{{-- Desktop menu (md and up) --}}
<nav class="c-menu-desktop" aria-label="Utama">
    <div class="c-menu-desktop_bar">
        <a href="/" class="c-menu-desktop_logo">{!! $logo !!}</a>
        <div class="c-menu-desktop_inner">
            <ul class="c-menu-desktop_list">
                @foreach ($links as $label => $href)
                    <li class="c-menu-desktop_item"><a href="{{ $href }}" class="c-menu-desktop_link" @if ($current($href)) aria-current="page" @endif><span class="c-menu-desktop_link_label">{{ $label }}</span></a></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div>
        <a href="/daftar" class="c-button-quote c-menu-desktop_contact">
            <span class="c-button-quote_label">Daftar sekarang</span>
        </a>
    </div>
</nav>

{{-- Mobile menu (below md) --}}
<div class="c-menu-mobile">
    <div class="c-menu-mobile_bar">
        <a href="/" class="c-menu-mobile_logo">{!! $logo !!}</a>
        <div class="c-menu-mobile_cta">
            <div><a href="/daftar" class="c-menu-mobile_contact max-2xs:hidden">Daftar</a></div>
            <button type="button" class="c-menu-mobile_burger" aria-label="Menu" aria-expanded="false" aria-controls="menu-mobile-nav">
                <span class="c-menu-mobile_burger_icon -default"><span class="c-menu-mobile_burger_icon_line"></span><span class="c-menu-mobile_burger_icon_line"></span></span>
                <span class="c-menu-mobile_burger_icon -close"><span class="c-menu-mobile_burger_icon_line"></span><span class="c-menu-mobile_burger_icon_line"></span></span>
            </button>
        </div>
    </div>
    <nav id="menu-mobile-nav" class="c-menu-mobile_nav" aria-label="Utama">
        <div class="c-menu-mobile_nav_inner" data-lenis-prevent>
            <ul class="c-menu-mobile_list">
                @foreach ($links as $label => $href)
                    <li class="c-menu-mobile_list_item" style="--index: {{ $loop->index }}"><a href="{{ $href }}" class="c-menu-mobile_nav_link" @if ($current($href)) aria-current="page" @endif><span class="c-menu-mobile_nav_label">{{ $label }}</span></a></li>
                @endforeach
            </ul>
            <a href="/daftar" class="c-menu-mobile_nav_contact">
                <span class="c-menu-mobile_nav_label c-menu-mobile_nav_contact_label">Daftar sekarang</span>
                <span class="c-menu-mobile_nav_contact_icon"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m8 14 4-4-4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </a>
        </div>
    </nav>
</div>
