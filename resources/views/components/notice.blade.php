@props([
    'title' => null,
    'icon' => null,
    'type' => '',
])
@php($attrs = $attributes->class([
    'notice',
    'notice--large' => (bool) $title,
    "notice-{$type}" => (bool) $type,
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
    {{ $title }}
</div>
@endif
