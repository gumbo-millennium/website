@php
$startTimestamp = $activity->start_date;
$endTimestamp = $activity->end_date;
$url = route('activity.show', ['activity' => $activity]);
$enrolled = 'Niet ingeschreven';
if (isset($enrollments[$activity->id])) {
    $enrolled = 'Ingeschreven';
    if (!$enrollments[$activity->id]->state->isStable()) {
        $enrolled = "Actie vereist!";
    }
}

// Determine duration
$duration = $endTimestamp->diffForHumans($startTimestamp, [
    'syntax' => Carbon\Carbon::DIFF_ABSOLUTE,
    'parts' => 1,
    'options' => Carbon\Carbon::ROUND
]);

// Determine price label
$price = Str::price($activity->total_price_member ?? 0);

if ($activity->total_price_member === null && $activity->total_price_guest === null) {
    // If it's free, mention it
    $price = 'gratis';
} elseif (!$activity->total_price_member && $activity->is_public) {
    // Free for members when public
    $price = 'gratis voor leden';
} elseif ($activity->total_price_member === $activity->total_price_guest) {
    // Same price for both parties
    $price = Str::price($activity->total_price_member);
} elseif ($activity->is_public) {
    // Starting bid
    $price = sprintf('vanaf %s', Str::price($activity->total_price_member ?? 0));
}
@endphp
<article class="activity-block {{ $activityClass ?? null }}">
    <div class="container activity-block__container">
        <div class="activity-block__date">
            <div class="activity-block__date-day">{{ $startTimestamp->isoFormat('dd') }}</div>
            <div class="activity-block__date-date">{{ $startTimestamp->isoFormat('DD') }}</div>
            <div class="activity-block__date-month">{{ $startTimestamp->isoFormat('MMM') }}</div>
        </div>
        <div class="activity-block__content">
            {{-- Title --}}
            <h3 class="activity-block__title">
                <a href="{{ route('activity.show', compact('activity')) }}">{{ $activity->name }}</a>
            </h3>

            <div class="flex-grow"></div>

            <div class="activity-block__details">
                {{-- Date and time --}}
                <div class="mr-4">
                    @icon('solid/calendar', 'icon-md icon-before')
                    {{ $startTimestamp->isoFormat('dddd D MMMM, YYYY') }}
                </div>
                <div class="mr-4">
                    @icon('solid/clock', 'icon-md icon-before')
                    {{ $startTimestamp->isoFormat('HH:mm') }}
                </div>

                {{-- Duration --}}
                <div class="mr-4">
                    @icon('solid/hourglass-half', 'icon-md icon-before')
                    {{ $duration }}
                </div>

                {{-- Break --}}
                <div class="w-full mb-4"></div>

                {{-- Cost --}}
                <div class="mr-4">
                    @icon('solid/ticket-alt', 'icon-md icon-before')
                    @if ($activity->available_seats)
                    {{ $price }}
                    @else
                    <span class="text-danger-light">Uitverkocht</span>
                    @endif
                </div>

                {{-- Places --}}
                @if ($activity->seats)
                {{-- Cost --}}
                <div class="mr-4">
                    @icon('solid/user-friends', 'icon-md icon-before')
                    {{ $activity->seats }} plekken
                </div>
                @endif

                {{-- User status --}}
                <div>
                    @icon('solid/user-check', 'icon-md icon-before')
                    {{ $enrolled }}
            </div>
        </div>
    </div>
</article>
