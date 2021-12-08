@php
    $actionText = 'Bekijk aanmelding';
@endphp

<x-mail::message>
{{-- Greeting --}}
<x-slot name="header">
    Aanmelding nieuw lid
</x-slot>

{{-- Intro Lines --}}
Beste {{ $order->user->first_name }},

Bedankt voor je bestelling bij Gumbo Millennium.

De bestelling:

<x-mail::table>
|Product|Aantal|Eenheidsprijs|Prijs|
|-------|------|-------------|-----|
@foreach($order->variants as $variant)
|{{ $variant->display_name }}|{{ $variant->pivot->quantity }}|{{ Str::price($variant->pivot->price) }}|{{ Str::price($variant->pivot->price * $variant->pivot->quantity) }}|
@endforeach
</x-mail::table>

Het bestuur zal binnenkort contact met je opnemen om de bestelling af te ronden.

{{-- Outro Lines --}}
<p class="text-gray-primary-1">
    Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.
</p>

Met vriendelijke groet,

De Digitale Commissie
</x-mail::message>
