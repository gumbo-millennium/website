@extends('layout.main')

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
    @component('components.enroll.header', ['activity' => $activity, 'enrollment' => $enrollment])
    <div class="pt-8 leading-relaxed text-lg flex flex-col gap-y-4">
        <p>
            Je bent ingeschreven voor {{ $activity->name }}.
        </p>
        <p>
            Hieronder vind je de details van je inschrijving.
        </p>
    </div>
    @endcomponent

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">

        @include('enrollments.partials.enrollment-data')

        <hr class="my-8 bg-gray-400" />

        @if ($enrollment->state instanceof \App\Models\States\Enrollment\Paid)
        <p class="notice notice--warning">
            Je kan je niet uitschrijven voor deze activiteit.
        </p>
        @else
        <form formaction="{{ route('enroll.cancel', [$activity]) }}" method="POST">
            @csrf

            <button type="submit" class="w-full btn btn--small m-0 text-center">
                @lang('Unenroll')
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
