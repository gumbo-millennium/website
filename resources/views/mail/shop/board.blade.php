@php
$actionText = 'Bekijk aanmelding';
@endphp

@component('mail::message')
{{-- Greeting --}}
@slot('header')
Aanmelding nieuw lid
@endslot

{{-- Intro Lines --}}
Geacht bestuur,

Er is een bestelling binnengekomen voor de merchandise van Gumbo Millennium.

De bestelling is voor {{ $order->user->name }}.

De bestelling:

@component('mail::table')
|Product|Aantal|Eenheidsprijs|Prijs|
|-------|------|-------------|-----|
@foreach($order->variants as $variant)
|{{ $variant->display_name }}|{{ $variant->pivot->quantity }}|{{ Str::price($variant->pivot->price) }}|{{ Str::price($variant->pivot->price * $variant->pivot->quantity) }}|
@endforeach
@endcomponent

{{-- Outro Lines --}}
<p class="text-gray-primary-1">
    Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.
</p>

Met vriendelijke groet,

De Digitale Commissie
@endcomponent
