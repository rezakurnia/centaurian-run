{{-- Closing row: three centred columns (same text everywhere). Pass 'class' for spacing. --}}
<footer class="body-md grid gap-gutter text-center md:grid-cols-3 {{ $class ?? '' }}">
    <div>Centaurian Run</div>
    <div>Fun run keluarga besar Alfa Centauri: siswa, guru, alumni, dan orang tua lari bersama.</div>
    <div>© {{ now()->year }} Alcent Centaurian Run. All rights reserved.</div>
</footer>
