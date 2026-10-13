{{-- Closing row: three centred columns (same text everywhere). Pass 'class' for spacing. --}}
<footer class="body-md grid gap-gutter text-center md:grid-cols-3 {{ $class ?? '' }}">
    <div>Centaurian Run</div>
    <div>Yayasan Taqwa Cerdas Kreatif</div>
    <div>© {{ now()->year }} Alcent Centaurian Run. All rights reserved.</div>
</footer>
