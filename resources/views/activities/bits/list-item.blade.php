@php
$startTimestamp = $activity->start_date;
$endTimestamp = $activity->end_date;
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

// Determine duration
$duration = $endTimestamp->diffForHumans($startTimestamp, [
    'syntax' => Carbon\Carbon::DIFF_ABSOLUTE,
    'parts' => 1,
    'options' => Carbon\Carbon::ROUND
]);

// Determine price label
$price = $activity->price_label;
@endphp
<article class="activity-block {{ $activityClass ?? null }}">
    <div class="container activity-block__container">
        <time datetime="{{ $startTimestamp->toIso8601String() }}" class="activity-block__date">
            <div class="activity-block__date-day">{{ $startTimestamp->isoFormat('dd') }}</div>
            <div class="activity-block__date-date">{{ $startTimestamp->isoFormat('DD') }}</div>
            <div class="activity-block__date-month">{{ $startTimestamp->isoFormat('MMM') }}</div>
        </time>
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
                    {{ $price }}
                </div>

                {{-- Places --}}
                @if ($activity->seats)
                <div class="mr-4">
                    @icon('solid/user-friends', 'icon-md icon-before')
                    @if ($isSoldOut)
                    <span class="text-red-700">Uitverkocht</span>
                    @else
                    {{ $activity->available_seats }} plekken beschikbaar
                    @endif
                </div>
                @endif

                {{-- User status --}}
                <div class="{{ $enrollmentClass }}">
                    @icon($enrolledIcon, 'icon-md icon-before')
                    {{ $enrolled }}
            </div>
        </div>
    </div>
</article>
