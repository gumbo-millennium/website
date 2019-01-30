@extends('main.layout.default')

{{-- Disable transparent navbar --}}
@push('navbar-classes')
navbar--opaque
@endpush

@php
$hour = (int) now()->format('H');
if ($hour < 5 || $hour >= 23) {
    $finalGreeting = "Maak er een leuke nacht van, en dan zien we je snel bij Gumbo Millennium.";
} elseif ($hour < 10) {
    $finalGreeting = "Maak er een geweldige dag van en hopelijk zien we je snel bij Gumbo Millennium.";
} elseif ($hour < 16) {
    $finalGreeting = "Geniet nog even van je dag, en hopelijk zien we je binnenkort Gumbo Millennium.";
} elseif ($hour < 18) {
    $finalGreeting = "Een héle fijne avond alvast en hopelijk tot snel bij Gumbo Millennium.";
} else {
    $finalGreeting = "Geniet lekker van je avond en hopelijk zien we je snel bij Gumbo Millennium.";
}
@endphp

@section('content')
<div class="hero hero--small py-4">
    <div class="hero__container">
        <h1 class="hero__header">
            Welkom bij Gumbo Millennium!
        </h1>
    </div>
</div>

{{-- Sign up form --}}
<div class="gumbo-shaded-block">
    <div class="container">
        <p class="mb-1 mt-4">
            je aanmelding is verstuurd. Alvast van harte welkom bij Gumbo Millennium {{ $name ?? '' }}.
        </p>
        <p>
            Je hebt een kopie van je aanmelding ontvangen op {{ $email ?? 'het opgegeven adres' }}.<br />
            Het bestuur van Gumbo Millennium neemt zo snel mogelijk contact met je op.
        </p>
        <p class="mt-2">
            {{ $finalGreeting }}
        </p>
        @guest
        <div class="alert alert-info mt-2">
            <strong>Tip:</strong> Maak alvast een account aan met hetzelfde e-mail adres, dan kan het bestuur je ook direct
            toegang geven tot de documenten en besloten activiteiten, en pak je die heerlijke ledenkorting op de activiteiten ook
            zo snel mogelijk mee!
        </div>
        @endguest
    </div>
</div>
@endsection
