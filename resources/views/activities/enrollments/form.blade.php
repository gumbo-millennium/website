@extends('layout.variants.two-col')

@section('title', "Inschrijven - {$activity->name} - Gumbo Millennium")

{{-- Set sidebar --}}
@section('two-col.right')
@component('activities.bits.sidebar', compact('activity'))
    @slot('showTagline', false)
    @slot('showMeta', true)
@endcomponent
@endsection

{{-- Set main --}}
@section('two-col.left')
<h1 class="text-3xl font-title mb-4">Inschrijven voor {{ $activity->name }}</h1>

<div class="leading-loose">
    <p class="mb-4">
        Gefeliciteerd, je hebt een plekje bemachtigd voor {{ $activity->name }}.

        @if ($enrollment->total_price)
        Je inchrijving is nog niet definitief. Hiervoor moet je eerst onderstaand formulier invullen en de inschrijfgelden betalen via iDEAl.
        @else
        Je inchrijving is bijna definitief, je hoeft alleen nog maar onderstaand formulier in te vullen.
        @endif
    </p>
    <p>
        Onderstaande gegevens worden verwerkt conform het <a title="Open het privacybeleid in een nieuw scherm"
            href="/privacy-policy" target="_blank" rel="noopener">Gumbo Millennium privacybeleid</a>.
    </p>
</div>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}
@endsection
