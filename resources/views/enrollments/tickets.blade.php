@extends('layout.main', ['hideFlash' => true])

@section('title', "Inschrijven voor {$activity->name}")

@php($hasPaidTicket = collect($tickets)->min('price'))

@section('content')
<div class="bg-gray-50">
    <x-enroll.header :activity="$activity">
        <div class="leading-relaxed text-lg flex flex-col gap-y-4">
            <p>
                Kies hieronder het ticket dat je wil bestellen voor {{ $activity->name }}.
            </p>
            <p>
                Je hebt na je keuze 15 min om je inschrijving af te ronden, anders komt je plek weer vrij.
            </p>
        </div>
    </x-enroll.header>

    @if ($activity->available_seats === 0)
    <div class="enroll-column">
        <div class="notice notice--large notice--warning my-0">
            <h3 class="notice__title">@lang('Sold Out')</h3>
            <p>
                @lang("This activity has no more seats available.")
                @lang("The ticket options below are just for reference.")
            </p>
        </div>
    </div>
    @elseif (! $hasTickets && $tickets->isNotEmpty())
    <div class="enroll-column">
        <div class="notice notice--large notice--warning my-0">
            <h3 class="notice__title">@lang('No tickets available')</h3>
            <p>
                @lang("Sorry, but there are currently no tickets available for this activity.")
                @lang("The ticket options below are just for reference.")
            </p>
        </div>
    </div>
    @endif

    <form action="{{ route('enroll.store', [$activity]) }}" method="POST" class="enroll-column mt-8">
        @csrf

        <div class="space-y-4 lg:grid lg:grid-cols-2 lg:gap-5 lg:space-y-0">
            @forelse($tickets as $ticket)
                <x-enroll.ticket :ticket="$ticket" />
            @empty
            <div class="flex flex-col overflow-hidden col-span-2">
                <div class="px-6 py-12 rounded-lg border-4 border-dashed">
                    <h3 class="text-2xl text-center text-gray-500">
                        @lang('No tickets available')
                    </h3>
                </div>
            </div>
            @endforelse
        </div>

        <div class="py-8">
            <a href="{{ route('activity.show', [$activity]) }}" class="btn btn--small text-center">
                @lang('Cancel and return to activity')
            </a>
        </div>
    </form>
</div>
@endsection
