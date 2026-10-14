{{-- Closing row: three centred columns (same text everywhere). Pass 'class' for spacing. Contacts come from admin Pengaturan. --}}
@php
    $s = app(\App\Services\SettingService::class)->getAllFlat();
    $links = array_filter([
        'WhatsApp' => $s['whatsapp_url'] ?? '',
        'Instagram' => $s['instagram_url'] ?? '',
        'Facebook' => $s['facebook_url'] ?? '',
    ]);
@endphp
<footer class="body-md grid gap-gutter text-center md:grid-cols-3 {{ $class ?? '' }}">
    <div>
        Centaurian Run
        @if (! empty($s['contact_email']))<br><a class="c-link" href="mailto:{{ $s['contact_email'] }}">{{ $s['contact_email'] }}</a>@endif
        @if (! empty($s['contact_phone']))<br>{{ $s['contact_phone'] }}@endif
    </div>
    <div>
        Yayasan Taqwa Cerdas Kreatif
        @if ($links)<br>@foreach ($links as $label => $url)<a class="c-link" href="{{ $url }}" target="_blank" rel="noopener">{{ $label }}</a>@if (! $loop->last) · @endif @endforeach @endif
    </div>
    <div>© {{ now()->year }} Alcent Centaurian Run. All rights reserved. <a class="c-link" href="/login">Login panitia</a></div>
</footer>
