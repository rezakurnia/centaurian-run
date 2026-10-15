{{-- Closing row: three centred columns (same text everywhere). Pass 'class' for spacing.
     Name, contact and social links come from Admin → Pengaturan; empty ones are left out. --}}
@php
    $s = app(\App\Services\SettingService::class)->getAllFlat();
    $contact = array_filter([
        'Email' => $s['contact_email'] ?? '' ? 'mailto:' . $s['contact_email'] : null,
        'Telepon' => $s['contact_phone'] ?? '' ? 'tel:' . preg_replace('/[^\d+]/', '', $s['contact_phone']) : null,
    ]);
    // Only http(s) links, so a stray "javascript:" value can't end up in a public href
    $social = array_filter(
        ['WhatsApp' => $s['whatsapp_url'] ?? '', 'Instagram' => $s['instagram_url'] ?? '', 'Facebook' => $s['facebook_url'] ?? ''],
        fn ($url) => preg_match('#^https?://#i', $url)
    );
@endphp
<footer class="body-md grid gap-gutter text-center md:grid-cols-3 {{ $class ?? '' }}">
    <div>
        <div>{{ ($s['app_name'] ?? '') ?: 'Centaurian Run' }}</div>
        @if ($contact)
            <div class="mt-1">
                @foreach ($contact as $label => $href)
                    <a class="c-link inline-block py-3" href="{{ $href }}">{{ $label === 'Email' ? $s['contact_email'] : $s['contact_phone'] }}</a>@if (! $loop->last)<span aria-hidden="true"> · </span>@endif
                @endforeach
            </div>
        @endif
    </div>
    <div>
        <div>Yayasan Taqwa Cerdas Kreatif</div>
        @if ($social)
            <div class="mt-1">
                @foreach ($social as $label => $href)
                    <a class="c-link inline-block py-3" href="{{ $href }}" target="_blank" rel="noopener">{{ $label }}</a>@if (! $loop->last)<span aria-hidden="true"> · </span>@endif
                @endforeach
            </div>
        @endif
    </div>
    <div>© {{ now()->year }} Alcent Centaurian Run. All rights reserved. <a class="c-link inline-block py-3" href="/login">Login panitia</a></div>
</footer>
