{{-- Desktop + mobile menus. $base is '' on the landing page and '/' on every other page, so the section links resolve to /#kategori. --}}
@php
    $base = $base ?? '';
    $sections = ['kategori', 'paket', 'jadwal', 'hasil'];
    // Desktop bar: label => href (section anchors live on the landing page)
    $links = ['Tentang' => $base . '#tentang', 'Kategori' => $base . '#kategori', 'Paket' => $base . '#paket', 'Jadwal' => $base . '#jadwal', 'Cek Status' => '/cek-status', 'Hasil Lomba' => '/hasil'];
    // Logo (shared by the desktop and mobile bars): 36x30 mark + wordmark
    $logo = '<span class="c-logo_mark"><img src="/images/logo.png" alt="" width="824" height="490"></span><span class="c-logo_text">Centaurian Run</span>';
@endphp

{{-- Desktop menu (md and up) --}}
<nav class="c-menu-desktop" aria-label="Utama">
    <div class="c-menu-desktop_bar">
        <a href="/" class="c-menu-desktop_logo">{!! $logo !!}</a>
        <div class="c-menu-desktop_inner">
            <ul class="c-menu-desktop_list">
                @foreach ($links as $label => $href)
                    <li class="c-menu-desktop_item"><a href="{{ $href }}" class="c-menu-desktop_link"><span class="c-menu-desktop_link_label">{{ $label }}</span></a></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div>
        <c-button-quote>
            <a href="/daftar" class="c-button-quote c-menu-desktop_contact">
                <span class="c-button-quote_label">Daftar sekarang</span>
                <span class="c-button-quote_icon"><canvas class="c-button-quote_icon_canvas" width="40" height="40" aria-hidden="true"></canvas></span>
            </a>
        </c-button-quote>
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
        <div class="c-menu-mobile_nav_inner">
            <ul class="c-menu-mobile_list">
                @foreach ($sections as $id)
                    <li class="c-menu-mobile_list_item" style="--index: {{ $loop->index }}"><a href="{{ $base }}#{{ $id }}" class="c-menu-mobile_nav_link"><span class="c-menu-mobile_nav_label">{{ ucfirst($id) }}</span></a></li>
                @endforeach
                <li class="c-menu-mobile_list_item" style="--index: 4"><a href="/daftar" class="c-menu-mobile_nav_link"><span class="c-menu-mobile_nav_label">Daftar</span></a></li>
                <li class="c-menu-mobile_list_item" style="--index: 5"><a href="/cek-status" class="c-menu-mobile_nav_link"><span class="c-menu-mobile_nav_label">Cek status</span></a></li>
                <li class="c-menu-mobile_list_item" style="--index: 6"><a href="/hasil" class="c-menu-mobile_nav_link"><span class="c-menu-mobile_nav_label">Hasil lomba</span></a></li>
            </ul>
            <a href="/daftar" class="c-menu-mobile_nav_contact">
                <span class="c-menu-mobile_nav_label c-menu-mobile_nav_contact_label">Daftar sekarang</span>
                <span class="c-menu-mobile_nav_contact_icon"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m8 14 4-4-4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </a>
        </div>
    </nav>
</div>
