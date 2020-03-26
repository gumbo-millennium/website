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

@foreach ($quotesList as list($date, $quotes))
<div class="block w-full mb-2 text-center">
    <h3 class="inline-block px-4 py-2 mx-auto font-sm rounded bg-gray-200 text-gray-800">{{ $date }}</h3>
</div>

@foreach ($quotes as $quote)
<blockquote class="mb-4">
    <p class="text-gray-600 font-serif mb-2 p-2 rounded bg-gray-200">{{ $quote->quote }}</p>
    <p>– {{ optional($quote->user)->name ?? "¨{$quote->display_name}„" ?? 'Onbekend' }}</p>
</blockquote>
@endforeach
@endforeach

Groetjes,

De Digitale Commissie
@endcomponent
