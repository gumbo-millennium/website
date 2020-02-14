@php
$startTimestamp = $activity->start_date;
$url = route('activity.show', ['activity' => $activity]);

// Is-checks
$isEnrolled = isset($enrollments[$activity->id]);
$isSoldOut = $activity->available_seats === 0;

// Enrollment state
$enrolled = 'Niet ingeschreven';
$enrolledIcon = 'solid/user-times';
$enrollmentClass = '';
if ($isEnrolled && $enrollments[$activity->id]->is_stable) {
    $enrolledIcon = 'solid/user-check';
    $enrolled = "Ingeschreven";
} elseif ($isEnrolled) {
    $enrolledIcon = 'solid/exclamation-triangle';
    $enrolled = "Actie vereist!";
    $enrollmentClass = 'text-red-700';;
}

// Determine price label
$price = $activity->price_label;
$seats = 'Onbeperkt plaats';
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
@endphp
<div class="activity-card">
    {{-- Header --}}
    <div class="activity-card__header">
        <div class="activity-card__header-item">
            @icon('solid/clock', 'icon mr-2')
            <time datetime="{{ $activity->start_date->toIso8601String() }}">{{ $date }}</time>
        </div>
        <div class="activity-card__header-item">
            @if ($isSoldOut)
                @icon('solid/times', 'icon mr-2')
                <span>Uitverkocht</span>
            @elseif ($activity->is_public)
                @icon('solid/globe', 'icon mr-2')
                <span>Openbaar</span>
            @else
                @icon('solid/user-friends', 'icon mr-2')
                <span>Besloten</span>
            @endif
        </div>
    </div>

    {{-- Main --}}
    <div class="activity-card__body">
        {{-- Icon --}}
        <div class="activity-card__body-icon">
            @if ($activity->image->exists())
            <img class="activity-card__body-icon-image" src="{{ $activity->image->url('poster') }}" srcset="
                    {{ $activity->image->url('poster-small') }} 96w,
                    {{ $activity->image->url('poster') }} 192w,
                    {{ $activity->image->url('poster-large') }} 384w
                " />
            @else
            <img class="activity-card__body-icon-image" src="{{ mix('images/logo-glass-green.svg') }}">
            @endif
        </div>

        {{-- Title and tagline --}}
        <h3 class="activity-card__body-title">
            <a href="{{ $url }}" class="activity-card__body-title-link stretched-link">{{ $activity->name }}</a>
        </h3>
        <p class="activity-card__body-tagline">{{ $activity->tagline }}</p>

        {{-- Users going --}}
        <div class="activity-card__body-user-list">
            {{-- TODO --}}
        </div>
    </div>

    {{-- Footer data --}}
    <div class="flex flex-row border-t border-gray-200 text-center">
        <div class="activity-card__detail">
            <p class="activity-card__detail-title">Prijs</p>
            <p class="activity-card__detail-value">{{ $price }}</p>
        </div>
        <div class="activity-card__detail">
            <p class="activity-card__detail-title">Aantal plekken</p>
            <p class="activity-card__detail-value">{{ $seats }}</p>
        </div>
    </div>
</div>
