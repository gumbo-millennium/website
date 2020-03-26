# Wist-Je-Datjes

{{-- Intro Lines --}}
Liebe, lieve Klass,

Bij deze alle wist je datjes van afgelopen periode.

@foreach ($quotesList as list($date, $quotes))
== {{ $date }} ==

@foreach ($quotes as $quote)
Door {{ optional($quote->user)->name ?? 'Onbekend' }}:
> {{ $quote->quote }}

@endforeach
@endforeach

Groetjes,

De Digitale Commissie

================================================

{{-- Outro Lines --}}
Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.

Als je niet snapt hoe je aan dit mailtje komt, dan is dat mooi jouw probleem :)
