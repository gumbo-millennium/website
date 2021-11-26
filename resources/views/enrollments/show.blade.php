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
<div class="container container--sm py-8">
    @component('components.events.enroll-header', ['activity' => $activity])
    @endcomponent

    <div class="my-8 leading-relaxed text-lg flex flex-col gap-y-4">
        <p>
            Hieronder zie je de details van je inschrijving.
        </p>
    </div>

    <dl class="grid grid-cols-3 gap-4 mb-8">
        @foreach ($settings as $title => $detail)
        <dt class="font-bold">{{ $title }}</dt>
        <dd class="col-start-2 col-end-4">{{ $detail }}</dd>
        @endforeach
    </dl>

    <form action="{{ route('enroll.cancel', [$activity]) }}" method="POST">
        @csrf

        <button type="submit" class="btn btn--small">
            Inschrijving annuleren
        </a>
    </form>
</div>
@endsection
