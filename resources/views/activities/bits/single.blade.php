@php
use App\Models\Activity;

$startTimestamp = $activity->start_date;
$url = route('activity.show', ['activity' => $activity]);

// Is-checks
$isEnrolled = isset($enrollments[$activity->id]);
$isSoldOut = $activity->available_seats === 0;

// Enrollment state
$statusText = 'Openbaar';
$statusIcon = 'solid/globe';
$statusStyle = '';

// Get a proper status
if ($isEnrolled && !$enrollments[$activity->id]->is_stable) {
    $statusText = 'Actie vereist';
    $statusIcon = 'solid/exclamation-triangle';
    $statusStyle = 'text-red-primary-2';
} elseif ($isEnrolled) {
    $statusText = "Ingeschreven";
    $statusIcon = 'solid/user-check';
} elseif ($isSoldOut) {
    $statusText = 'Uitverkocht';
    $statusIcon = 'solid/times';
} elseif (!$activity->is_public) {
    $statusText = 'Besloten';
    $statusIcon = 'solid/user-friends';
}

// Determine price label
$price = $activity->price_range;
$seats = 'Onbeperkt';
if ($activity->seats > 0 && !$isSoldOut && ($activity->available_seats < 10)) {
    $seats = sprintf(
        'Nog %d %s',
        $activity->available_seats,
        $activity->available_seats === 1 ? 'plek' : 'plekken'
    );
} elseif ($activity->seats > 0) {
    $seats = "{$activity->seats} plekken";
}
$date = $activity->start_date->isoFormat('DD MMM, HH:mm');

// Add bottom data
$footerData = [];
if ($activity->location && $activity->location_address) {
    $footerData[] = $activity->location;
}

if ($activity->total_price !== null || $activity->seats !== null) {
    $footerData[] = ucfirst($seats);
    $footerData[] = ucfirst($price);
}

$urlClass = ['stretched-link'];
if ($activity->is_cancelled) {
    $urlClass[] = 'line-through';
}
$urlClass = implode(' ', $urlClass);

$features = $activity->expanded_features;

$bannerImage = image_asset($activity->poster)->preset('banner');
$bannerImage2x = (clone $bannerImage)->dpr(2);

@endphp
<div class="card">
    <div class="card__figure" role="presentation">
        @if ($activity->poster)
        <img
            class="card__figure-image"
            src="{{ $bannerImage }}"
            srcset="{{ $bannerImage }} 384w, {{ $bannerImage2x }} 768w">
        @else
        <div class="card__figure-wrapper">
            <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="h-16 mx-auto block dark:hidden">
            <img src="{{ mix('images/logo-text-night.svg') }}" alt="Gumbo Millennium" class="h-16 mx-auto hidden dark:block">
        </div>
        @endif

        {{-- Badges --}}
        <div class="card__figure-badges">
            @if ($activity->is_cancelled)
                <span class="card__figure-badge card__figure-badge--danger">Geannuleerd</span>
            @elseif ($activity->is_rescheduled && $activity->rescheduled_from > now())
                <span class="card__figure-badge card__figure-badge--warning">Verplaatst</span>
            @elseif ($activity->is_postponed)
                <span class="card__figure-badge card__figure-badge--warning">Uitgesteld</span>
            @endif
        </div>

        {{-- Icons --}}
        @if ($features->isNotEmpty())
        <div class="absolute bottom-4 right-4 rounded-lg bg-white flex items-center space-x-2 p-2">
            @foreach ($features as $feature)
            @icon("solid/{$feature->icon}", [
                'class' => 'icon h-4 card__figure-icon',
                'title' => $feature->title
            ])
            @endforeach
        </div>
        @endif
    </div>

    <div class="card__body">
        <div class="card__body-label card__list">
            <time datetime="{{ $activity->start_date->toIso8601String() }}">{{ $date }}</time>
        </div>

        <h2 class="card__body-title">
            <a href="{{ $url }}" class="{{ $urlClass }}">{{ $activity->name }}</a>
        </h2>

        <p class="card__body-content">{{ $activity->tagline }}</p>

        <div class="card__body-meta card__list">
            @foreach ($footerData as $item)
                <div>{{ $item }}</div>

                @if (!$loop->last)
                <div class="card__list-separator">&bull;</div>
                @endif
            @endforeach
        </div>
    </div>
</div>
