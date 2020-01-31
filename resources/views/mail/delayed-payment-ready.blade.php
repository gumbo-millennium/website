@component('mail::message')
@slot('title')
Klaar voor betaling
@endslot

@slot('reason')
Je krijgt deze mail omdat er iets fout ging bij het starten van je betaling.
@endslot

## Je kunt betalen

Je probeerde eerder vandaag een betaling te starten voor {{ $activity->name }} van {{ Str::price($enrollment->total_price) }}.

Dit is nog niet gelukt, of je hebt deze betaling nog niet afgerond.

Via onderstaande knop kan je meteen met iDEAL betalen via de {{ $bank }}.
De link logt je ook automatisch in op de webite.

@component('mail::button', ['url' => $targetUrl])
Nu betalen
@endcomponent

Bovenstaande link verloopt over 24 uur.

Heb je ondertussen al betaald of heb je je uitgeschreven? Dan mag je deze mail als niet-verzonden beschouwen.
@endcomponent
