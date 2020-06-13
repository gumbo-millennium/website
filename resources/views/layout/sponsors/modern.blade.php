@php
$sponsorClass = Str::slug("sponsor--brand-{$sponsor->slug}");
$logo = $sponsorService->toSvg($sponsor, [
    'class' => 'sponsor__card-logo-img',
    'title' => $sponsor->name,
    'aria-label' => "Logo van {$sponsor->name}"
], 'color');
@endphp
<div class="sponsor sponsor--backdrop sponsor--backdrop-brand" style="background-image: url('{{ $sponsor->backdrop->url('banner') }}');">
    <div class="container sponsor__container sponsor__container--modern">
        <div class="sponsor__card">
            {{-- Image --}}
            <figure class="sponsor__card-logo">
                {{ $logo }}
            </figure>

            {{-- Text --}}
            <p class="sponsor__card-text">
                {{ Str::words($sponsor->caption, 40) }}
            </p>

            {{-- Button --}}
            <a href="{{ route('sponsors.link', compact('sponsor')) }}" class="btn btn--brand sponsor__card-btn">
                Bekijk sponsor
            </a>
        </div>
    </div>
</div>
