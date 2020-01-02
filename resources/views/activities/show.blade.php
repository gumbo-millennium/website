@extends('layout.main')

@php
// Dates
$locale = ['nl', 'nl_NL', 'dutch'];

// Start date
$startLocale = $activity->start_date->locale(...$locale);
$startIso = $activity->start_date->toIso8601String();
$startDate = $startLocale->isoFormat('D MMM Y');
$startTime = $startLocale->isoFormat('HH:mm');
$startDateFull = $startLocale->isoFormat('dddd D MMMM Y, [om] HH:mm');

// Duration
$duration = $activity->start_date->diffAsCarbonInterval($activity->end_date);
$durationIso = $duration->spec();
$durationTime = $duration->locale(...$locale)->forHumans(['parts' => 1]);

$memberPrice = $activity->price_member ? Str::price($activity->total_price_member) : 'gratis';
$guestPrice = $activity->price_guest ? Str::price($activity->total_price_guest) : 'gratis';
$priceLabel = $activity->is_free ? 'Gratis toegang' : Str::ucfirst($activity->price_label);
$tagline = $activity->tagline ?? "{$startDateFull}, {$priceLabel}.";

// Enrollment open
$isOpen = $activity->enrollment_open;
$places = $user && $user->is_member ? $activity->available_seats : $activity->available_guest_seats;
$hasRoom = $places > 0;
$hasRoomMember = $activity->available_seats > 0;
@endphp

@section('title', "{$activity->name} - Activity - Gumbo Millennium")

@section('content')
<div class="header">
    <div class="header__floating" role="presentation">
        {{ trim($startLocale->isoFormat('D MMM'), '. ') }}
    </div>
    <div class="container header__container">
        <h1 class="header__title">{{ $activity->name }}</h1>
        <h3 class="header__subtitle">{{ $tagline }}</h3>
    </div>
</div>
<div class="container">
    <div class="flex flex-col md:flex-row">
        <div class="px-8 w-64 border-gray-800 border-r flex flex-col items-center">
            <time datetime="{{ $startIso }}" class="text-xl">{{ $startLocale->isoFormat('DD MMM \'YY') }}</time>

            <dl class="activity-facts">
                <dt class="activity-facts__fact">Datum</dt>
                <dd class="activity-facts__detail">
                    <time datetime="{{ $startIso }}">{{ $startDate }}</time>
                </dd>

                <dt class="activity-facts__fact">Aanvang</dt>
                <dd class="activity-facts__detail">
                    {{ $startTime }}
                </dd>

                <dt class="activity-facts__fact">Duur</dt>
                <dd class="activity-facts__detail">
                    <time datetime="{{ $durationIso }}">{{ $durationTime }}</time>
                </dd>

                <dt class="activity-facts__fact">Prijs leden</dt>
                <dd class="activity-facts__detail">
                    <data value="{{ ($activity->price_member ?? 0) / 100 }}">{{ $memberPrice }}</data>
                </dd>

                @if ($activity->is_public)
                <dt class="activity-facts__fact">Prijs niet-leden</dt>
                <dd class="activity-facts__detail">
                    <data value="{{ ($activity->price_guest ?? 0) / 100 }}">{{ $guestPrice }}</data>
                </dd>
                @endif
            </dl>
        </div>
        <div class="p-8 md:mr-8 flex-grow">
            <h1 class="text-4xl font-bold">{{ $activity->name }}</h1>

            {!! $activity->description_html !!}

            @if ($user && $is_enrolled)
            <a href="{{ route('enroll.show', compact('activity')) }}" class="btn btn--brand">{{ $is_stable ? 'Beheer inschrijving' : 'Inschrijving afronden' }}</a>
            @elseif (!$hasRoom)
            <button class="btn btn--brand btn--disabled" disabled>Uitverkocht</button>
            @if ($hasRoomMember)
            <p class="text-gray-700">
                Er is nog wel plek voor leden.
                @guest
                Ben je lid? <a href="{{ route('login') }}">Log dan in</a> om je aan te melden.
                @else
                <a href="{{ route('join') }}">Word lid</a> om je aan te melden.
                @endguest
            </p>
            @endif
            @elseif (!$isOpen)
            <button class="btn btn--brand btn--disabled" disabled>Inschrijvingen gesloten</button>
            @else
            <form action="{{ route('enroll.create', compact('activity')) }}" method="post">
                @csrf
                <button type="submit" class="btn btn--brand">Inschrijven</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
