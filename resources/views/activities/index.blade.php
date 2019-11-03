@extends('layout')

@section('title', 'Activity overview - Gumbo Millennium')

@section('content')
<h1>Activity overview</h1>

<p>This is what we're going to do soon</p>
<ul style="list-style: '- ' outside;" class="pl-2">
    @foreach ($activities as $activity)
    @php
    $startTimestamp = ($activity->start_date ?? today())->locale('nl', 'nl_NL', 'dutch');
    $startDate = $startTimestamp->isoFormat('D MMM Y');
    $startTime = $startTimestamp->isoFormat('HH:mm');
    $url = route('activity.show', ['activity' => $activity]);
    @endphp
    <li class="pl-2 pb-4">
        <a href="{{ $url }}">{{ $activity->name }}</a><br />
        Datum: {{ $startDate }}<br />
        Tijd: {{ $startTime }}<br />
        Ingeschreven: {{ $enrollments->has($activity->slug) ? 'Ja' : 'Nee' }}
    </li>
    @endforeach
</ul>

<p>
    @if ($past)
    <a href="{{ route('activity.index') }}">Toon alleen toekomstige evenementen</a>
    @else
    <a href="{{ route('activity.index', ['past' => true]) }}">Toon afgelopen evenementen</a>
    @endif
</p>
@endsection
