@component('mail::message')
{{-- Greeting --}}
@slot('header')
Wist-Je-Datjes
@endslot

{{-- Subcopy --}}
@slot('subcopy')
Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.\
Als lid van de PRPG krijg je deze mailtjes, hiervoor kan je je niet afmelden.
@endslot

{{-- Intro Lines --}}
Liebe, lieve Klass,

Bij deze alle wist je datjes van afgelopen periode.
De quotes zijn ook als bijlage in de mail gezet, voor het gemak.

@foreach ($quotesList as list($date, $quotes))
<div class="block w-full mb-2 text-center">
    <h3 class="inline-block px-4 py-2 mx-auto font-sm rounded bg-gray-secondary-2 text-gray-primary-3">{{ $date }}</h3>
</div>

@foreach ($quotes as $quote)
<blockquote class="mb-4">
    <p class="text-gray-primary-1 font-serif mb-2 p-2 rounded bg-gray-secondary-2">{{ $quote->quote }}</p>
    <p>– {{ optional($quote->user)->name ?? "¨{$quote->display_name}„" ?? 'Onbekend' }}</p>
</blockquote>
@endforeach
@endforeach

Groetjes,

De Digitale Commissie
@endcomponent
