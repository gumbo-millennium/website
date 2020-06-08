@php
$logo = $sponsorService->toSvg($sponsor, [
    'class' => 'partner-link__logo',
    'title' => $sponsor->name,
    'aria-label' => "Logo van {$sponsor->name}"
], 'color');
@endphp
<div class="partner-block__item">
    <a href="{{ route('sponsors.show', compact('sponsor')) }}" class="partner-link">
        {{ $logo }}
    </a>
</div>
