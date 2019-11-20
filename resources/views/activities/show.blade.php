@extends('layout.main')

@php
// Dates
$locale = ['nl', 'nl_NL', 'dutch'];

// Start date
$startLocale = $activity->start_date->locale(...$locale);
$startIso = $activity->start_date->toIso8601String();
$startDate = $startLocale->isoFormat('D MMM Y');
$startTime = $startLocale->isoFormat('HH:mm');

// Duration
$duration = $activity->start_date->diffAsCarbonInterval($activity->end_date);
$durationIso = $duration->spec();
$durationTime = $duration->locale(...$locale)->forHumans(['parts' => 1]);

$memberPrice = $activity->price_member ? Str::price($activity->price_member) : 'gratis';
$guestPrice = $activity->price_guest ? Str::price($activity->price_guest) : 'gratis';
@endphp

@section('title', "{{ $activity->name }} - Activity - Gumbo Millennium")

@section('content')
<h1>{{ $activity->name }}</h1>

<h2>In het kort</h2>
<dl class="activity-facts">
    <dt>Datum</dt>
    <dd><time datetime="{{ $startIso }}">{{ $startDate }}</time></dd>
    <dt>Aanvang</dt>
    <dd>{{ $startTime }}</dd>
    <dt>Duur</dt>
    <dd><time datetime="{{ $durationIso }}">{{ $durationTime }}</time></dd>
    <dt>Prijs leden</dt>
    <dd><data value="{{ ($activity->price_member ?? 0) / 100 }}">{{ $memberPrice }}</data></dd>
    <dt>Prijs niet-leden</dt>
    <dd><data value="{{ ($activity->price_guest ?? 0) / 100 }}">{{ $guestPrice }}</data></dd>
</dl>

<h2>Details</h2>

{!! $activity->description_html !!}

<h2>Jouw inschrijving</h2>
{{--
    There are a couple of states the user can be in:
    1. Not logged in (show login button)
    2. Not verified (the user needs a valid email adress to sign up)
    3. Not enrolled, but able to enroll (enrollment_start < time < enrollment_end)
    4. Not enrolled, and enrollments are closed (time < enrollment_start || time > enrollment_end)
    5. Not enrolled, but locked out of enrollment (status->locked === true) [TODO]
    6. Enrolled, and able to unenroll (before enrollment_end date)
    7. Enrolled, unable to unenroll (after enrollment_end date)
--}}
@php
$viewBase = "activities.bits";
// Scenario (1)
$viewName = "{$viewBase}.guest";
if ($user) {
    // Scenario (2)
    $viewName = "{$viewBase}.verify";
    if ($user->hasVerifiedEmail()) {
        // Scnenario (3)
        $viewName = "{$viewBase}.enroll-open";
        if ($is_enrolled && $activity->enrollment_end < now()) {
            // Scenario (7)
            $viewName = "{$viewBase}.unenroll-closed";
        } elseif ($is_enrolled) {
            // Scenario (6)
            $viewName = "{$viewBase}.unenroll-open";
        } elseif ($activity->enrollment_end < now() || $activity->enrollment_start > now()) {
            // Scenario (4)
            $viewName = "{$viewBase}.enroll-closed";
        }
    }
}

$viewData = ['activity' => $activity];
@endphp

@include($viewName, $viewData)
@endsection
