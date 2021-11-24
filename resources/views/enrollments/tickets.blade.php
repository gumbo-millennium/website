@extends('layout.main')

@section('title', "Inschrijven voor {$activity->name}")

@section('content')
<div class="container container--sm py-8">
    @component('events.enroll-header')

    <form action="{{ route('enroll.store', [$activity]) }}" method="POST" class="border border-b-0 border-gray-200 rounded shadow">
        @csrf

        @foreach ($tickets as $ticket)
        <div class="flex items-center py-2 px-4 border-b border-gray-200">
            <div class="flex-grow">
                <h3 class="font-title text-2xl font-bold">
                    {{ $ticket->title }}
                </h3>

                <p class="text-gray-800">
                    {{ $ticket->description }}
                </p>
            </div>

            <button type="submit" name="ticket_id" value="{{ $ticket->id }}" class="btn btn--brand mr-2">
                @if ($ticket->price > 0)
                    {{ Str::price($ticket->total_price) }}
                @else
                    Kies ticket
                @endif
            </button>
        </div>
        @endforeach
    </form>
</div>
@endsection
