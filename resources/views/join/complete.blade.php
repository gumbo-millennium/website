@extends('layout.variants.login')

{{-- Get stuff --}}
@php
$hour = (int) now()->format('H');
$email = $submission->email;
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

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Hello <strong class="login__title-fat">you</strong> 😍</h1>
<p class="login__subtitle">Je aanmelding is binnen! Alvast welkom bij Gumbo Millennium.</p>

<div class="leading-relaxed">
    <p class="mb-2">Hey {{ $submission->first_name }},</p>

    <p class="mb-2">
        Je hebt een kopie van je aanmelding ontvangen op {{ $email ?? 'het opgegeven adres' }}.<br />
        Het bestuur van Gumbo Millennium neemt zo snel mogelijk contact met je op.
    </p>

    <p class="mb-4">
        {{ $finalGreeting }}
    </p>
</div>

<div class="notice notice--brand mb-4">
    <p class="my-0">
        <strong>Tip:</strong> Maak alvast een account aan met hetzelfde e-mail adres, dan kan het bestuur je ook direct
        toegang geven tot de documenten en besloten activiteiten, en pak je die heerlijke ledenkorting op de activiteiten ook
        zo snel mogelijk mee!
    </p>
</div>

<a href="/" class="btn btn--brand btn--wide">Door naar de homepage</a>
@endsection
