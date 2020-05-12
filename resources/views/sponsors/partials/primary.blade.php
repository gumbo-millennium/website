@php
$logo = $sponsorService->toSvg($sponsor, [
    'class' => 'partner-card__logo',
    'title' => $sponsor->name,
    'aria-label' => "Logo van {$sponsor->name}"
], 'color');
$backdrop = $sponsor->backdrop->url('backdrop');
@endphp
<article class="partner-block__item">
    <div class="partner-card">
        <div class="partner-card__image-wrapper">
            @if ($backdrop)
            <img
                role="presentation"
                class="partner-card__image"
                src="{{ $backdrop }}" />
            @endif
        </div>
        <div class="partner-card__main">
            {{-- Image --}}
            {{ $logo }}

            {{-- Body --}}
            <div class="partner-card__body">
                {{ Str::words($sponsor->caption, 12) }}
            </div>

            {{-- Link --}}
            <a href="{{ route('sponsors.show', compact('sponsor')) }}" class="partner-card__link stretched-link">
                <span>{{ $sponsor->name }}</span>
                @icon('chevron-right', 'h-4 ml-2')
            </a>
        </div>
    </div>
</article>
