@extends('layout.main', ['hideFlash' => true])

@section('title', "{$activity->name} - Activity - Gumbo Millennium")

<?php
$isCoronacheck = Arr::get($activity->features, 'coronacheck', false);
$ticketPrices = $activity->tickets
    ->pluck('total_price')
    ->sort()
    ->unique()
    ->map(fn ($price) => Str::price($price) ?? __('Free'));

if ($ticketPrices->isEmpty()) {
    $ticketPrices = 'Geen prijzen bekend';
} elseif ($ticketPrices->count() == 1) {
    $ticketPrices = $ticketPrices->first();
} elseif ($ticketPrices->count() == 2) {
    $ticketPrices = $ticketPrices->join(' of ');
} else {
    $ticketPrices = sprintf('van %s t/m %s', $ticketPrices->first(), $ticketPrices->last());
}
?>

@section('content')
<div class="">
    @component('components.page.header', ['image' => $activity->poster])
        @slot('title', $activity->name)

        @slot('header')
        <div class="mt-[-2rem] mb-4 px-8">
            <p class="text-lg text-gray-700">
                {{ $activity->tagline }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 border-t border-gray-200">
            @component('components.activities.header-tile', ['icon' => 'solid/calendar'])
                @slot('title', $activity->start_date->isoFormat('ddd DD MMMM, HH:mm'))

                @if ($activity->end_date->diffInHours($activity->start_date) < 6)
                <p>tot {{ $activity->end_date->isoFormat('HH:mm') }}</p>
                @else
                <p>tot {{ $activity->end_date->isoFormat('ddd DD MMMM, HH:mm') }}</p>
                @endif
            @endcomponent

            @component('components.activities.header-tile', ['icon' => 'solid/ticket-alt'])
                @slot('title', $ticketPrices)

                <p>{{ $activity->tickets->count() }} soorten tickets</p>
            @endcomponent

            @component('components.activities.header-tile', ['icon' => $activity->is_public ? 'solid/globe-europe' : 'solid/user-friends'])
                @slot('title', $activity->is_public ? 'Openbare activiteit' : 'Besloten activiteit')

                @if ($activity->is_public)
                <p>Iedereen is welkom</p>
                @else
                <p>Alleen voor leden</p>
                @endif
            @endcomponent

            <div class="grid grid-cols-1 lg:hidden">
                @component('components.activities.header-tile', ['icon' => 'solid/map-marked-alt'])
                    @slot('title', $activity->location)

                    <p>{{ $activity->location_address }}</p>
                @endcomponent
            </div>
        </div>
        @endslot

        @if ($isCoronacheck)
        <div class="notice notice--large notice--warning mx-8">
            <h3 class="notice__title">Testen voor Toegang</h3>
            <p>Om aan deze activiteit deel te nemen, moet je aan de deur een geldige CoronaCheck QR-code kunnen tonen.</p>
        </div>
        @endif

        {{-- Unlisted --}}
        @if (!$activity->is_published)
        <div class="notice notice--brand mx-8">
            Deze activiteit is nog niet gepubliceerd, alleen gebruikers met de link kunnen hem vinden.
        </div>
        @endif

        <div class="p-8">
            @if (!empty($activity->description_html))
            <div class="leading-relaxed plain-content prose">
                {!! $activity->description_html !!}
            </div>
            @else
            <p class="leading-relaxed text-center text-gray-primary-1">
                Deze activiteit heeft geen uitgebreide omschrijving.
            </p>
            @endif
        </div>

        <hr class="mx-8 bg-gray-200" />

        <div class="px-8 flex flex-col gap-4 items-stretch text-center md:flex-row">
            @if ($enrollment = Enroll::getEnrollment($activity))
                <a href="{{ route('enroll.show', [$activity]) }}" class="btn btn--brand max-w-1/2 md:flex-grow">
                    @if ($enrollment->is_stable)
                        Bekijk inschrijving
                    @else
                        Verder met inschrijving
                    @endif
                </a>

                @if ($enrollment->is_stable && Enroll::canTransfer($enrollment))
                    <a href="{{ route('enroll.transfer', [$activity]) }}" class="btn md:flex-grow">
                        Overdragen
                    </a>
                @endif
            @elseif ($activity->tickets->count() == 0)
                <button class="disabled btn">
                    Inschrijven niet mogelijk
                </button>
            @else
                <a href="{{ route('enroll.create', [$activity]) }}" class="btn btn--brand">
                    Inschrijven
                </a>
            @endif
        </div>
    @endcomponent
@endsection
