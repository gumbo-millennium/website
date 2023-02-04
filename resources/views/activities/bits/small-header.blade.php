
@php
// Start date
$startLocale = $activity->start_date;
$startIso = $activity->start_date->toIso8601String();
$startDate = $startLocale->isoFormat('D MMM Y');
$startDateFull = $startLocale->isoFormat('dddd D MMMM Y, [om] HH:mm');
@endphp
<div class="activity-summary__card">
    <div class="activity-summary__main activity-summary__main--horizontal">
        <div class="activity-summary__titles">
            {{-- Title --}}
            <h2 class="activity-summary__title">
                <a href="{{ route('activity.show', compact('activity')) }}">{{ $activity->name }}</a>
            </h2>
            @if (!empty($activity->tagline))
            <h3 class="activity-summary__subtitle">{{ $activity->tagline }}</h3>
            @endif
        </div>

        {{-- Details --}}
        <div class="activity-summary__stat-group">
            <div class="activity-summary__stat">
                <x-icon icon="solid/clock" class="mr-4" />
                <time datetime="{{ $startIso }}">{{ $startDate }}</time>
            </div>
            <div class="activity-summary__stat">
                <x-icon icon="solid/location-dot" class="mr-4" />
                @empty($activity->location)
                <span class="text-gray-500">Onbekend</span>
                @elseif ($activity->location_url)
                <a href="{{ $activity->location_url }}" target="_blank" rel="noopener">{{ $activity->location }}</a>
                @else
                {{ $activity->location }}
                @endif
            </div>
        </div>
    </div>
</div>
