@php
    $actionText = 'Bekijk aanmelding';
@endphp

@component('mail::message')
{{-- Greeting --}}
@slot('header')
    Aanmelding nieuw lid
@endslot

{{-- Intro Lines --}}
Beste {{ $order->user->first_name }},

Bedankt voor je bestelling bij Gumbo Millennium.

De bestelling:

@component('mail::table')
|Product|Aantal|Eenheidsprijs|Prijs|
|-------|------|-------------|-----|
@foreach($order->variants as $variant)
|{{ $variant->display_name }}|{{ $variant->pivot->quantity }}|{{ Str::price($variant->pivot->price) }}|{{ Str::price($variant->pivot->price * $variant->pivot->quantity) }}|
@endforeach
@endcomponent

Het bestuur zal binnenkort contact met je opnemen om de bestelling af te ronden.

{{-- Outro Lines --}}
<p class="text-gray-500">
    Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.
</p>

Met vriendelijke groet,

De Digitale Commissie
@endcomponent
