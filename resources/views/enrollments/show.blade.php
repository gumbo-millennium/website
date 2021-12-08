@extends('layout.main', ['hideFlash' => true])

@section('title', "Beheer inschrijving voor {$activity->name}")

<?php
$confirmed = __('Yes');
if (! $enrollment->state->isStable()) {
    $confirmed = __('No, expires at :expiration', [
        'expiration' => $enrollment->expire->isoFormat('DD MMM, HH:mm'),
    ]);
}
$settings = array_filter([
    __('Ticket Number') => $enrollment->id,
    __('Confirmed') => $confirmed,
    __('Enrolled at') => $enrollment->created_at->isoFormat('dddd DD MMMM, HH:mm'),
    __('State') => $enrollment->state->title,
    __('Price') => Str::price($enrollment->price) ?? __('Free'),
    __('Ticket') => $enrollment->ticket->title,
])
?>
@section('content')
<div class="bg-gray-50">
    <x-enroll.header :activity="$activity" :enrollment="$enrollment">
        <div class="leading-relaxed text-lg flex flex-col gap-y-4">
            <p>
                Je bent ingeschreven voor {{ $activity->name }}.
            </p>
            <p>
                Hieronder vind je de details van je inschrijving.
            </p>
        </div>
    </x-enroll.header>

    <hr class="mt-8 bg-gray-200" />

    <div class="py-4">
        @include('enrollments.partials.enrollment-data')
    </div>

    <hr class="mb-8 bg-gray-200" />

    <div class="grid grid-cols-1 gap-4 items-stretch text-center md:grid-cols-2">
        <div class="grid grid-cols-1 text-center max-w-1/2 md:flex-grow relative">
            @if ($enrollment->state instanceof \App\Models\States\Enrollment\Paid)
            <button class="btn" type="button" disabled>
                Uitschrijven niet mogelijk
            </button>
            @else
            <button class="btn" form="unenroll-form" type="submit">
                Uitschrijven
            </button>
            <form formaction="{{ route('enroll.cancel', [$activity]) }}" method="POST" id="unenroll-form">
                @csrf
            </form>
            @endif
        </div>

        <div class="grid grid-cols-1 text-center max-w-1/2 md:flex-grow">
            @if ($enrollment->is_stable && Enroll::canTransfer($enrollment))
                <a href="{{ route('enroll.transfer', [$activity]) }}" class="btn md:flex-grow">
                    Overdragen
                </a>
            @else
            <button class="btn" type="button" disabled>
                Overdragen niet mogelijk
            </button>
            @endif
        </div>
    </div>
    @endcomponent


    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">
        <a href="{{ route('activity.show', [$activity]) }}">
            Terug naar activiteit
        </a>
    </div>
</div>
@endsection
