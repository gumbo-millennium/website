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

// Determine price label
$price = sprintf('vanaf %s', Str::price(min(
    $activity->total_price_member ?? 0,
    $activity->total_price_guest ?? 0,
)));
if ($activity->total_price_member === null && $activity->total_price_guest === null) {
    $price = 'gratis';
} elseif ($activity->total_price_member === null) {
    $price = 'gratis voor leden';
} elseif ($activity->total_price_member === $activity->total_price_guest) {
    $price = Str::price($activity->total_price_member);
}
@endphp
<article class="mb-8 p-4 rounded bg-white shadow">
    <div class="flex flex-row">
        <div class="flex flex-col text-center items-center justify-center p-4 uppercase leadin1g-none">
            <div class="mb-2 font-bold text-xs text-brand-900">{{ $startTimestamp->isoFormat('dd') }}</div>
            <div class="mb-2 font-normal text-4xl text-brand-700">{{ $startTimestamp->isoFormat('DD') }}</div>
            <div class="mb-0 font-bold text-md text-brand-900">{{ $startTimestamp->isoFormat('MMM') }}</div>
        </div>
        <div class="flex-grow flex flex-col justify-between p-4">
            {{-- Title --}}
            <h3 class="text-2xl font-bold my-0 mb-4">
                <a href="{{ route('activity.show', compact('activity')) }}">{{ $activity->name }}</a>
            </h3>

            <div class="flex-grow"></div>

            {{-- Date and time --}}
            <div class="leading-none mb-4 flex flex-row text-gray-500">
                <div class="mr-4">
                    @icon('solid/calendar', 'icon-md icon-before')
                    {{ $startTimestamp->isoFormat('dddd D MMMM, YYYY') }}
                </div>
                <div class="mr-4">
                    @icon('solid/clock', 'icon-md icon-before')
                    {{ $startTimestamp->isoFormat('HH:mm') }}
                </div>
                <div class="mr-4">
                    @icon('solid/hourglass-half', 'icon-md icon-before')
                    {{ $endTimestamp->diffForHumans($startTimestamp, Carbon\CarbonInterface::DIFF_ABSOLUTE, false, 1) }}
                </div>
            </div>

            {{-- Cost --}}
            <div class="leading-none flex flex-row text-gray-500">
                <div class="mr-4">
                    @icon('solid/ticket-alt', 'icon-md icon-before')
                    {{ $price }}
                </div>
                <div>
                    @icon('solid/user', 'icon-md icon-before')
                    {{ $enrolled }}
            </div>
        </div>
    </div>
</article>
