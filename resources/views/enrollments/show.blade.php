@extends('layout')

@section('title', 'Activity overview - Gumbo Millennium')

@section('content')
<h1>My enrollments</h1>

<p>You've been enrolled into {{ $count }} enrollments.</p>

<ul style="list-style: '- ' outside;" class="pl-2">
    @foreach ($enrollments as $enrollment)
    @php
    // Get activity and URL
    $activity = $enrollment->activity;
    $url = route('enrollments.show', compact('activity'));

    // Get dates for the activity and the enrollment
    $enrollDate = $enrollment->created_at->isoFormat('D MMM Y, HH:mm');
    $enrollDateIso = $enrollment->created_at->toIso8601String();
    $activityDate = $activity->created_at->isoFormat('D MMM Y, HH:mm');
    $activityDateIso = $activity->created_at->toIso8601String();
    @endphp
    <li class="pl-2 pb-4">
        <a href="{{ $url }}">{{ $activity->name }}</a><br />
        Datum: <time datetime="{{ $activityDateIso }}">{{ $activityDate }}</time><br />
        Ingeschreven op: <time datetime="{{ $enrollDateIso }}">{{ $enrollDate }}</time><br />
        Status: {{ $enrollment->state->name }}
    </li>
    @endforeach
</ul>
@endsection
