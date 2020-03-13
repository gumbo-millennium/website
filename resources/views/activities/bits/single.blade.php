@php
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
    $statusStyle = 'text-red-700';
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
$price = $activity->price_label;
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

// Link class
$linkClass = 'activity-card__body-title-link stretched-link';
if ($activity->cancelled) {
    $linkClass .= ' activity-card__body-title-link--cancelled';
}
@endphp
<div class="activity-card">
    {{-- Header --}}
    <div class="activity-card__header">
        <div class="activity-card__header-item">
            @icon('solid/clock', 'icon mr-2')
            <time datetime="{{ $activity->start_date->toIso8601String() }}">{{ $date }}</time>
        </div>
        <div class="activity-card__header-item {{ $statusStyle }}">
            @icon($statusIcon, 'icon mr-2')
            <span>{{ $statusText }}</span>
        </div>
    </div>

    {{-- Main --}}
    <div class="activity-card__body">
        {{-- Icon --}}
        <div class="activity-card__body-icon">
            @if ($activity->image->exists())
            <img
                class="activity-card__body-icon-image"
                alt="Foto van {{ $activity->name }}"
                src="{{ $activity->image->url('poster') }}" srcset="
                    {{ $activity->image->url('poster-half') }} 96w,
                    {{ $activity->image->url('poster') }} 192w,
                    {{ $activity->image->url('poster-2x') }} 384w
                " sizes="
                    (min-width: 1280px) 288px,
                    (min-width: 768px) and (max-width: 1280px) 210px,
                    (min-width: 640px) and (max-width: 768px) 164px,
                480px
                " />
            @else
            <img
                src="{{ mix('images/logo-glass-green.svg') }}"
                alt="Gumbo Millennium logo, omdat geen foto aanwezig is voor {{ $activity->name }}"
                class="activity-card__body-icon-image"
                />
            @endif
        </div>

        {{-- Title and tagline --}}
        <h3 class="activity-card__body-title">
            <a href="{{ $url }}" class="{{ $linkClass }}">{{ $activity->name }}</a>
        </h3>
        @if ($activity->is_cancelled)
            <p class="activity-card__body-notice activity-card__body-notice--danger">Geannuleerd</p>
        @endif
        <p class="activity-card__body-tagline">{{ $activity->tagline }}</p>

        {{-- Users going --}}
        <div class="activity-card__body-user-list">
            {{-- TODO --}}
        </div>
    </div>

    {{-- Footer data --}}
    @if (!$activity->is_cancelled)
    <div class="flex flex-row border-t border-gray-200 text-center">
        <div class="activity-card__detail">
            <p class="activity-card__detail-title">Prijs</p>
            <p class="activity-card__detail-value">{{ ucfirst($price) }}</p>
        </div>
        <div class="activity-card__detail">
            <p class="activity-card__detail-title">Aantal plekken</p>
            <p class="activity-card__detail-value">{{ ucfirst($seats) }}</p>
        </div>
    </div>
    @endif
</div>
