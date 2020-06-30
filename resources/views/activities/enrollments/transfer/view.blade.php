@extends('layout.variants.two-col')

@section('title', "Inschrijving overdragen - {$activity->name} - Gumbo Millennium")

@php
$transferText = "Wil jij mijn inschrijving voor {$activity->name} overnemen?";
@endphp

{{-- Set sidebar --}}
@section('two-col.right')
@component('activities.bits.sidebar', compact('activity'))
@slot('showTagline', true)
@slot('showMeta', true)
@slot('nextLink', 'activity')
@endcomponent
@endsection

{{-- Set main --}}
@section('two-col.left')
<h1 class="text-3xl font-title mb-4">Inschrijving overdragen</h1>

<div class="leading-loose">
    <p class="mb-4">
        Je kunt je inschrijving voor {{ $activity->name }} overdragen tot aanvang van de activiteit. Daarna is
        overdragen niet meer mogelijk.
    </p>
    <p class="mb-8">
        Deel onderstaande link met de persoon die je wilt uitnodigen. Diegene kan dan na inloggen de inschrijving
        overnemen.
    </p>

    <div class="rounded bg-gray-secondary-2 px-8 py-4 text-center">
        @if ($transferLink)
        <a href="{{ $transferLink }}" target="_blank" data-action="share" data-share-url="{{ $transferLink }}"
            data-share-text="{{ $transferText }}">{{ $transferLink }}</a>
        @else
        <span class="text-gray-primary-3">Je hebt nog geen transferlink aangevraagd</span>
        @endif
    </div>

    <div class="flex flex-row items-center justify-center">
        <button type="submit" form="transfer-delete" class="btn mr-4">Deactiveren</button>
        @if ($enrollment->transfer_secret)
        <button type="submit" form="transfer-update" class="btn">Vernieuwen</button>
        @else
        <button type="submit" form="transfer-update" class="btn btn--brand">Genereren</button>
        @endif
    </div>

    {{-- Forms --}}
    <form class="hidden" id="transfer-update" name="transfer-update"
        action="{{ route('enroll.transfer', compact('activity')) }}" method="post">
        @csrf
    </form>
    <form class="hidden" id="transfer-delete" name="transfer-delete"
        action="{{ route('enroll.transfer', compact('activity')) }}" method="post">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
