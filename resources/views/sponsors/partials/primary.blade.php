@php
$logo = $sponsorService->toSvg($sponsor, [
    'class' => 'partner-card__image-logo',
    'title' => $sponsor->name,
    'aria-label' => "Logo van {$sponsor->name}"
], 'color');

$backdrop = image_url($sponsor->cover)->preset('banner');
@endphp
<article class="partner-block__item">
    <div class="partner-card">
        <div class="partner-card__image-wrapper">
            @if ($backdrop)
            <img
                role="presentation"
                class="partner-card__image-backdrop"
                src="{{ $backdrop }}" />
            @endif
            <div class="partner-card__image-logo-wrapper">
                {{ $logo }}
            </div>
        </div>
        <div class="partner-card__main">
            {{-- Title --}}
            <h3 class="partner-card__main-title">{{ $sponsor->contents_title }}</h3>

            {{-- Link --}}
            <a href="{{ route('sponsors.show', compact('sponsor')) }}" class="partner-card__link stretched-link">
                <span>{{ $sponsor->name }}</span>
                @icon('solid/chevron-right', 'h-4 ml-2')
            </a>
        </div>
    </div>
</article>
