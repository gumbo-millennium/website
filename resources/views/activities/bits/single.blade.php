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
if ($isSoldOut) {
    $seats = 'Uitverkocht';
} elseif ($activity->seats > 0) {
    $seats = "{$activity->seats} plekken";
}
@endphp
<div class="activity-card">
    {{-- Image --}}
    <div class="activity-card__image-wrapper">
        @if ($activity->image->exists())
        <img
            class="activity-card__image"
            src="{{ $activity->image->url('poster') }}"
            srcset="{{ $activity->image->url('poster') }}, {{ $activity->image->url('poster@2x') }}" />
        @else
        <img
            class="activity-card__image activity-card__image--empty"
            src="{{ mix('images/logo-text-white.svg') }}">
        @endif
    </div>

    {{-- Data --}}
    <div class="activity-card__body">
        {{-- Header --}}
        <h2 class="activity-card__title">{{ $activity->name }}</h2>

        <p class="activity-card__lead">{{ $activity->tagline }}</p>

        {{-- Stats --}}
        <div class="activity-card__facts">
            {{-- Date --}}
            <div class="activity-card__fact">
                @icon('solid/calendar', 'activity-card__fact-icon')
                <time datetime="{{ $startTimestamp->toIso8601String() }}">{{ $startTimestamp->isoFormat('ddd D MMM') }}</time>
            </div>
            {{-- Time --}}
            <div class="activity-card__fact">
                @icon('solid/clock', 'activity-card__fact-icon')
                <span class="activity-card__fact-label">{{ $startTimestamp->isoFormat('HH:mm') }}</span>
            </div>
            {{-- Price --}}
            <div class="activity-card__fact">
                @icon('solid/ticket-alt', 'activity-card__fact-icon')
                <span class="activity-card__fact-label">{{ $price }}</span>
            </div>
            {{-- Slots --}}
            <div class="activity-card__fact">
                @icon('solid/user-friends', 'activity-card__fact-icon')
                <span class="activity-card__fact-label">{{ $seats }}</span>
            </div>
        </div>

        {{-- Link --}}
        <a href="{{ $url }}" class="activity-card__read-more">
            <span class="activity-card__read-more-label">Naar activiteit</span>
            @icon('solid/angle-right', 'activity-card__read-more-icon')
        </a>
    </div>
</div>
