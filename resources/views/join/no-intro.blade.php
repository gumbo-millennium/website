@extends('layout.variants.login')

@php
  $started = $activity && Date::now()->greaterThan($activity->started_at);
  $soldOut = $activity && $activity->available_seats === 0;
  $tooEarly = $activity && Date::now()->lessThan($ticket?->enrollment_start ?? $activity->enrollment_start);
  $tooLate = $activity && Date::now()->greaterThan($ticket?->enrollment_end ?? $activity->enrollment_end);

  $startDiffInDays = $activity?->start_date->diffInDays();
  $startDiffInHours = $activity?->start_date->diffInHours();
  $startDiffLabel = $startDiffInDays > 1 ? "{$startDiffInDays} dagen" : "{$startDiffInHours} uur";
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Oh no, <strong>slecht nieuws</strong></h1>
@if ($soldOut)
<p class="login__subtitle">De introductieweek zit rammetjevol.</p>
@elseif ($tooLate || $tooEarly || $started)
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
    @if ($started)
    <p class="leading-relaxed mb-2" data-test="too-late">
      De introductieweek van Gumbo Millennium is gestart. Je kan je dus niet meer hiervoor inschrijven.
    </p>
    @elseif ($soldOut)
    <p class="leading-relaxed mb-2" data-test="too-late">
      De introductieweek van Gumbo Millennium is helaas uitverkocht. Je kan je dus niet meer hiervoor inschrijven.
    </p>
    @elseif ($tooLate)
    <p class="leading-relaxed mb-2" data-test="too-late">
        De inschrijving start over {{ $startDiffLabel }}. Omdat je inschrijving verwerken tijd
        kost en dit vaak wat ruimer van tevoren plaatsvind, is het niet mogelijk om je via de website last-minute in
        te schrijven.
    </p>
    <div class="notice notice--large notice--brand">
        <h3 class="notice__title">Toch last-minute inschrijven?</h3>
        <p class="text-lg">Als je je last-minute wil inschrijven, raden we je aan om te bellen naar <strong>038 845 0100</strong>.</p>
    </div>
    @elseif ($tooEarly)
    <p class="leading-relaxed mb-2" data-test="too-early">
        Je kan je momenteel nog niet inschrijven voor de intro van Gumbo Millennium.<br />
        De inschrijvingen voor de introductieweek openen op <strong>{{ $activity->enrollment_start->isoFormat('dddd D MMMM') }}</strong>,
        we zien je graag dan terug!
    </p>
    @elseif($activity)
    <p class="leading-relaxed mb-2" data-test="too-late">
      Je kan je momenteel nog niet inschrijven voor de intro van Gumbo Millennium.<br />
      Helaas is er nog geen datum bekend waarop de inschrijvingen starten.
      Houd deze pagina in de gaten voor meer info, of stuur ons een berichtje!
    </p>
    @else
    <p class="leading-relaxed mb-2" data-test="no-intro">
      Je kan je momenteel nog niet inschrijven voor de intro van Gumbo Millennium.<br />
      Zodra de datum bekend is, zal deze pagina weer beschikbaar zijn.
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
