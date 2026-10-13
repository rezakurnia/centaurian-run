{{-- Closing row: three centred columns (same text everywhere). Pass 'class' for spacing. --}}
<footer class="body-md grid gap-gutter text-center md:grid-cols-3 {{ $class ?? '' }}">
    <div>Centaurian Run</div>
    <div>Move with Faith, Run with Care: gerakan YTCK untuk kebenaran, keadilan, dan kemanusiaan.</div>
    <div>© {{ now()->year }} Alcent Centaurian Run. All rights reserved.</div>
</footer>
