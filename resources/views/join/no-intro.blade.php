@extends('layout.variants.login')

@php
    $isClosed = $intro !== null;
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Oh no, <strong>slecht nieuws</strong></h1>
@if ($isClosed)
<p class="login__subtitle">De inschrijvingen voor de intro zijn gesloten.</p>
@else
<p class="login__subtitle">Er is momenteel nog geen intro gepland.</p>
@endif

{{-- Add intro --}}
<div class="mb-4">
    <p class="leading-relaxed mb-2">
        Hallo daar!
    </p>
    <p class="leading-relaxed mb-2">
        Wat leuk dat je je wil aanmelden voor de intro van Gumbo Millennium.
    </p>
    @if ($isClosed)
    <p class="leading-relaxed mb-2">
        De inschrijving start over {{ $intro->start_date->diffInHours() }} uur. Omdat je inschrijving verwerken tijd
        kost en dit vaak wat ruimer van tevoren plaatsvind, is het niet mogelijk om je via de website last-minute in
        te schrijven.
    </p>
    <div class="notice notice--large notice--brand">
        <h3 class="notice__title">Toch last-minute inschrijven?</h3>
        <p class="text-lg">Als je je last-minute wil inschrijven, raden we je aan om te bellen naar <strong>038 845 0100</strong>.</p>
    </div>
    @else
    <p class="leading-relaxed mb-2">
        Helaas kan je momenteel niet online inschrijven, omdat er momenteel nog
        geen introductieweek is gepland. Deze zal naar alle waarschijnlijkheid
        in de volgende herfstvakantie zijn.
    </p>
    @endif

    <h4 class="font-title text-lg mt-8 mb-2">Dan maar zonder intro?</h4>

    <p class="mt-4 leading-relaxed">
        Introductieweek of niet, je bent altijd van harte welkom om je in te schrijven als lid van Gumbo Millennium.
    </p>

    <div>
        <a href="{{ route('join.form') }}" class="btn btn--brand">Word lid</a>
    </div>
</div>
@endsection
