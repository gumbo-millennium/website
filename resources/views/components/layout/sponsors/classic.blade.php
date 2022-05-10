@php
$logo = Sponsors::toSvg($sponsor, [
    'class' => 'sponsor__simple-logo fill-current',
    'title' => $sponsor->name,
    'aria-label' => "Logo van {$sponsor->name}"
]);
@endphp

@unless (empty($logo))
<div class="sponsor">
    <div class="container sponsor__container">
        <a href="{{ route('sponsors.link', compact('sponsor')) }}" target="_blank" rel="noopener" class="sponsor__simple-link">
            {{-- Load SVG from platform --}}
            {{ $logo }}
        </a>
    </div>
</div>
@endunless
