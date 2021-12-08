<?php
$actionText = 'Bekijk aanmelding';
?>
<x-mail::message>
{{-- Greeting --}}
<x-slot name="header">
Aanmelding nieuw lid
</x-slot>

{{-- Intro Lines --}}
Geacht bestuur,

Er is een bestelling binnengekomen voor de merchandise van Gumbo Millennium.

De bestelling is voor {{ $order->user->name }}.

Je kan de details zien op de website, onder nummer **{{ $order->number }}**.

De bestelling:

<x-mail::table>
|Product|Aantal|Eenheidsprijs|Prijs|
|-------|------|-------------|-----|
@foreach($order->variants as $variant)
|{{ $variant->display_name }}|{{ $variant->pivot->quantity }}|{{ Str::price($variant->pivot->price) }}|{{ Str::price($variant->pivot->price * $variant->pivot->quantity) }}|
@endforeach
</x-mail::table>

{{-- Outro Lines --}}
<p class="text-gray-primary-1">
    Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.
</p>

Met vriendelijke groet,

De Digitale Commissie
</x-mail::message>
