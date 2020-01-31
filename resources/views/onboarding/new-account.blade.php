@extends('layout.variants.basic')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Welkom <strong>{{ $user->first_name }}</strong></h1>
<p class="text-lg text-gray-700 mb-4">Bedankt voor het aanmaken van een account.</p>

<p>
    Je account is aangemaakt, maar voordat je je kan aanmelden voor activiteiten en het
    documentensysteem kunt bekijken (als je lid bent), moet je eerst even je e-mailadres
    bevestigen.
</p>
<div class="my-4 p-4 border border-brand-600 rounded">
    <p>
        Klik op <strong>de link in je mail</strong> om je e-mailadres te bevestigen.
    </p>
</div>

<p>Om door te gaan naar de website, kan je hieronder klikken.</p>

<a href="{{ $nextUrl }}" class="btn btn--brand">Doorgaan</a>

@endsection
