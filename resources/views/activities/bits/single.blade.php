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
} elseif ($activity->seats > 0 && ($activity->available_seats < 10)) {
    $seats = sprintf(
        'Nog %d %s',
        $activity->available_seats,
        $activity->available_seats === 1 ? 'plek' : 'plekken'
    );
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
            srcset="
                {{ $activity->image->url('poster-small') }} 96w,
                {{ $activity->image->url('poster') }} 192w,
                {{ $activity->image->url('poster-large') }} 384w
            " />
        @else
        <img
            class="activity-card__image activity-card__image--empty"
            src="{{ mix('images/logo-text-white.svg') }}">
        @endif
    </div>

    {{-- Data --}}
    <div class="activity-card__body">
        {{-- Header --}}
        <h2 class="activity-card__title">
            <a href="{{ $url }}" class="activity-card__title-link">{{ $activity->name }}</a>
        </h2>

        <p class="activity-card__lead">{{ $activity->tagline }}</p>

        {{-- Stats --}}
        <div class="activity-card__facts">
            {{-- Date --}}
            <div class="activity-card__fact">
                @icon('solid/clock', 'activity-card__fact-icon')
                <time datetime="{{ $startTimestamp->toIso8601String() }}">{{ $startTimestamp->isoFormat('ddd D MMM, HH:mm') }}</time>
            </div>
            {{-- Time --}}
            <div class="activity-card__fact">
                @icon('solid/map-marker-alt', 'activity-card__fact-icon')
                <div class="activity-card__fact-label">
                    @empty($activity->location)
                    <span class="activity-card__fact-label text-gray-600">Onbekend</span>
                    @elseif ($activity->location_url)
                    <a href="{{ $activity->location_url }}" target="_blank" rel="noopener">{{ $activity->location }}</a>
                    @else
                    {{ $activity->location }}
                    @endif
                </div>
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
