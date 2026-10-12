{{-- Usage: @include('partials.btn', ['href' => '/daftar', 'label' => 'Daftar', 'bg' => 'var(--color-red)', 'primary' => true]) --}}
@php($arrow = '<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="m8 14 4-4-4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>')
<a href="{{ $href }}" class="btn {{ ($primary ?? false) ? 'btn-primary' : '' }}" @isset($bg) style="--btn-bg: {{ $bg }}" @endisset>
    <span class="btn-icon -left">{!! $arrow !!}</span>
    <span class="btn-label">{{ $label }}</span>
    <span class="btn-icon -right">{!! $arrow !!}</span>
</a>
