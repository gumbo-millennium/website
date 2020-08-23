@extends('layout.variants.basic')

@php
$driverName = ucfirst($link->driver);
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Verbinden met <strong>{{ $driverName }}</strong></h1>
<p class="text-lg text-gray-primary-2 mb-4">Koppel je {{ $driverName }} account met je Gumbo Millennium account</p>

<p class="leading-loose mb-2">
    Door je {{ $driverName }} account te koppelen aan je Gumbo account, kan je via de bot
    jezelf in- en uitschrijven van activiteiten, wist-je-datjes terugtrekken (als je het op tijd doet) en bestanden doorzoeken.
</p>

<p class="leading-loose mb-4 text-sm">Functionaliteiten onder voorbehoud en afhankelijk van je rechten op de website.</p>

{{-- Pending quotes --}}
@forelse ($links as $link)
    <div class="p-4 rounded border-brand-secondary-1 flex flex-row items-center">
        @svg("brands/{$link->icon}.svg", "h-16 mr-8 text-brand-primary-1")
        <div class="flex-grow">
            <h3 class="font-bold mb-4 text-brand-primary-1">{{ $link->name }} ({{ $link->driver }})</h3>
            <p>
                Gekoppeld op {{ $link->isoFormat('DDD MMMM YY, HH:mm T') }}
                <a href="{{ route('account.bot-users.unlink', compact('link')) }}">Ontkoppelen</a>
            </p>
        </div>
    </div>
@empty
<div class="text-center p-16">
    <h2 class="text-3xl text-gray-primary-1 text-center">Geen koppelingen</h2>
    <p class="text-lg text-gray-secondary-3 text-center">Praat met een Gumbot om een koppeling te maken</p>
</div>
@endforelse

@endsection
