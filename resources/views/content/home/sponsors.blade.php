@inject('sponsorService', 'App\\Services\\SponsorService')

@if ($homeSponsors->count() === 4)
<div class="bg-gray-secondary-2">
    <div class="container home-sponsors__container">
        @foreach ($homeSponsors as $sponsor)
        <div class="home-sponsors__sponsor">
            <a href="{{ route('sponsors.link', compact('sponsor')) }}" target="_blank" class="home-sponsor__sponsor-link">
                {{ $sponsorService->toSvg($sponsor, ['class' => 'home-sponsors__sponsor-image']) }}
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
