# Wist-Je-Datjes

{{-- Intro Lines --}}
Lieve leden van de Gumbode,

Bij deze alle wist je datjes van afgelopen periode.
De quotes zijn ook als bijlage in de mail gezet, voor het gemak.

@foreach ($quotesList as [$date, $quotes])
== {{ $date }} ==

@foreach ($quotes as $quote)
Door {{ $quote->user?->name ?? "¨{$quote->display_name}„" ?? 'Onbekend' }}:
> {{ $quote->quote }}

@endforeach
@endforeach

Groetjes,

De Digitale Commissie

================================================

{{-- Outro Lines --}}
Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.

Als redactielid van de Gumbode krijg je deze mailtjes, hiervoor kan je je lekker niet afmelden.
