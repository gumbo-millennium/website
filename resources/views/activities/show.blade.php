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
$visibilityIcon = $activity->is_public ? 'solid/globe-europe' : 'solid/user-friends';
$visibilityTitle = $activity->is_public ? "Openbare activiteit" : "Besloten activiteit";
?>

@section('content')
<div class="">
    <x-page.header :image="$activity->poster" :title="$activity->name">
        <x-slot name="headerIcon">
            <div class="hidden lg:block">
                <div class="group flex-none ml-4 relative">
                    <div aria-hidden="true" class="hidden group-hover:flex absolute top-0 h-8 right-8 bg-white px-2 items-center justify-end text-gray-600 w-64">
                        <strong class="font-bold">{{ $visibilityTitle }}</strong>
                    </div>
                    <x-icon :icon="$visibilityIcon" class="text-gray-400 h-8" aria-label="{{ $visibilityTitle }}" />
                </div>
            </div>
        </x-slot>

        <x-slot name="header">
            <div class="mt-[-2rem] mb-4 px-8">
                <p class="text-lg text-gray-700">
                    {{ $activity->tagline }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 border-t border-gray-200">
                <x-activities.header-tile icon="solid/calendar">
                    <x-slot name="title">
                        {{ $activity->start_date->isoFormat('ddd DD MMMM, HH:mm') }}
                    </x-slot>

                    @if ($activity->end_date->diffInHours($activity->start_date) < 6)
                    <p>tot {{ $activity->end_date->isoFormat('HH:mm') }}</p>
                    @else
                    <p>tot {{ $activity->end_date->isoFormat('ddd DD MMMM, HH:mm') }}</p>
                    @endif
                </x-activities.header-tile>

                <x-activities.header-tile icon="solid/ticket-alt" :title="$ticketPrices">
                    <p>{{ $activity->tickets->count() }} soorten tickets</p>
                </x-activities.header-tile>

                <div class="grid grid-cols-1 lg:hidden">
                    <x-activities.header-tile :icon="$visibilityIcon" :title="$visibilityTitle" />
                </div>

                <x-activities.header-tile icon="solid/map-marked-alt" :title="$activity->location">
                    <p>{{ $activity->location_address }}</p>
                </x-activities.header-tile>
            </div>
        </x-slot>

        {{-- Activity has ended --}}
        @if ($activity->end_date < Date::now())
        <x-notice class="mx-8">
            Deze activiteit is al afgelopen.
        </x-notice>
        @else
            {{-- Coronacheck --}}
            @if ($isCoronacheck)
            <x-notice type="warning" title="Testen voor Toegang" class="mx-8">
                Om aan deze activiteit deel te nemen, moet je aan de deur een geldige CoronaCheck QR-code kunnen tonen.
            </x-notice>
            @endif

            {{-- Unlisted --}}
            @if (!$activity->is_published)
            <x-notice type="brand" class="mx-8">
                Deze activiteit is nog niet gepubliceerd, alleen gebruikers met de link kunnen hem vinden.
            </x-notice>
            @endif
        @endif

        <div class="p-8">
            @if (!empty($activity->description_html))
            <div class="leading-relaxed prose">
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
                <a href="{{ route('enroll.show', [$activity]) }}" class="btn btn--brand max-w-1/2 md:flex-grow" data-action="view-enrollment">
                    @if ($enrollment->is_stable)
                        Bekijk inschrijving
                    @else
                        Verder met inschrijving
                    @endif
                </a>

                @if ($enrollment->is_stable && Enroll::canTransfer($enrollment))
                    <a href="{{ route('enroll.transfer', [$activity]) }}" class="btn md:flex-grow" data-action="transfer-enrollment">
                        Overdragen
                    </a>
                @endif
            @elseif ($activity->tickets->count() < 1 || $activity->end_date < Date::now())
                <button class="disabled btn">
                    Inschrijven niet mogelijk
                </button>
            @else
                <a href="{{ route('enroll.create', [$activity]) }}" class="btn btn--brand" data-action="enroll">
                    Inschrijven
                </a>
            @endif
        </div>
    </x-page.header>

    <x-activities.ical-link />
@endsection
