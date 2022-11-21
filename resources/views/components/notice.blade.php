@props([
    'title' => null,
    'icon' => null,
    'type' => '',
])
@php($attrs = $attributes->class([
    'notice',
    'notice--large' => (bool) $title,
    "notice-{$type}" => (bool) $type,
    // Type is one of (for Tailwind purge):
    // notice-warning, notice-info, notice-success, notice-error, notice-brand
]))
@if ($title)
<div {{ $attrs }}>
    <h3 class="notice__title">{{ $title }}</h3>
    <p>
        {{ $slot }}
    </p>
</div>
@else
<div {{ $attrs }}>
    {{ $slot }}
</div>
@endif
