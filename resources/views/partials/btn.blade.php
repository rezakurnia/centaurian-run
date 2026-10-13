{{-- Usage: @include('partials.btn', ['href' => '/daftar', 'label' => 'Daftar', 'color' => 'red', 'primary' => true])
     color: green|yellow|pink|red|blue. primary = filled with the colour, otherwise white with a coloured icon. --}}
@php($arrow = '<span class="c-icon"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m8 14 4-4-4-4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>')
<a href="{{ $href }}" class="c-button button-{{ $color ?? 'green' }} {{ ($primary ?? false) ? 'button-primary' : 'button-secondary' }} {{ $class ?? '' }}" {{ $attributes ?? '' }}>
    <span class="c-button_icon -hover">{!! $arrow !!}</span>
    <span class="c-button_label">{{ $label }}</span>
    <span class="c-button_icon -default">{!! $arrow !!}</span>
</a>
