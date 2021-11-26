@extends('layout.main')

@section('title', "Inschrijven voor {$activity->name}")

@php($hasPaidTicket = collect($tickets)->min('price'))

@section('content')
<div class="container container--sm py-8">
    @component('components.events.enroll-header', ['activity' => $activity])
    @endcomponent

    <div class="my-8 leading-relaxed text-lg flex flex-col gap-y-4">
        <p>
            Kies hieronder het ticket dat je wil bestellen voor {{ $activity->name }}.
        </p>
        <p>
            Je hebt na je keuze 15 min om je inschrijving af te ronden, anders komt je plek weer vrij.
        </p>
    </div>

    <form action="{{ route('enroll.store', [$activity]) }}" method="POST" class="border border-b-0 border-gray-200 rounded shadow mb-8">
        @csrf

        @foreach ($tickets as $ticket)
        <div class="flex items-center py-2 px-4 gap-x-4 border-b border-gray-200">
            <div class="flex-grow">
                <h3 class="font-title text-2xl font-bold">
                    {{ $ticket->title }}
                </h3>

                <p class="text-gray-800">
                    {{ $ticket->description }}
                </p>
            </div>

            <div>
                @if ($ticket->price > 0)
                    {{ Str::price($ticket->total_price) }}
                @elseif ($hasPaidTicket)
                    Gratis
                @else
                    Kies ticket
                @endif
            </div>

            <button type="submit" name="ticket_id" value="{{ $ticket->id }}" class="btn btn--brand btn--small flex items-center mr-2">

                @icon('solid/chevron-right', 'h-4')
            </button>
        </div>
        @endforeach
    </form>

    <div class="w-full grid grid-cols-1 lg:block">
        <a href="{{ route('activity.show', [$activity]) }}" class="btn btn--small text-center">
            Annuleren en terug naar activiteit
        </a>
    </div>
</div>
@endsection
