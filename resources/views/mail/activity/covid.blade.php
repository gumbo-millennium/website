@component('mail::message')

@slot('summary')
Laatste informatie voor {{ $activity->name }}
@endslot

{{-- Image --}}
@slot('mailImage', mix('images/mail/header-covid.png'))

Beste {{ $participant->first_name }},

Het is bijna zover, over {{ $activity->start_date->diffInHours() }} uur begint
{{ $activity->name }}. Maar voor het zover is, willen we je nog even wijzen
op de huisregels voor activiteiten die plaatsvinden tijdens deze corona-tijden.

In deze mail staat ook wat te doen bij ziekte, dus lees 'm even goed door (we
houden het kort).

@slot('html')
{{-- Keep distance --}}
@component('mail::icon-tile')
@slot('icon', mix('images/mail/mail-covid-distance.png'))
@slot('iconAlt', "Houd afstand")
@slot('title', 'Bewaar de afstand en blijf zitten')

<p>
    Net zoals op straat moet je bij de activiteiten 1,5 meter afstand van
    elkaar houden. In café's en op terrassen mag je met z'n tweeën aan één
    tafel zitten, maar laat de stoelen op hun plek staan, zodat de afstand ook
    gegarandeerd blijft.
</p>

<p>
    Eten en drinken bestel je bij de SC-leden of de organisatie van de
    activiteit.  <strong>Ga dus niet zelf naar de bar lopen</strong>.
</p>
@endcomponent

{{-- No singing --}}
@component('mail::icon-tile')
@slot('icon', mix('images/mail/mail-covid-no-sing.png'))
@slot('iconAlt', "Niet zingen")
@slot('title', 'Niet zingen en geen Gumbo yell')

<p>
    Het is in kerken en andere gebedshuizen verboden om te zingen voor
    deelnemers, omdat dit veel aerosolen in de lucht brengt waarmee het virus
    zich verspreid.  Omdat wij als verenigingsactiviteit momenteel in een
    relatief grijs gebied zitten, handhaven wij dezelfde regels.
</p>

<p>
    Dit betekent dus dat er niet gezongen mag worden (binnen óf buiten) en dat
    je de Gumbo yell ook helaas even achterwege moet laten. We snappen dat een
    speech zonder "Wat een spreker" een hele andere ervaring is, maar het is
    helaas even niet anders.
</p>
@endcomponent

{{-- Stay home --}}
@component('mail::icon-tile')
@slot('icon', mix('images/mail/mail-covid-stay-home.png'))
@slot('iconAlt', "Blijf thuis")
@slot('title', 'Blijf thuis bij ziekte of ziekteverschijnselen')

<p>
    Heb je last van verkoudheid, keelpijn en hoesten, koorts of andere aan
    coronavirus-gerelateerde klachten? Blijf dan thuis.
</p>

@if ($cancelType == 'delete')
<p>
    Je kan je met onderstaande knop uitschijven voor deze activiteit.
</p>

@component('mail::button', ['url' => $cancelUrl])
    Uitschrijven
@endcomponent
@else
<p>
    Je kan je voor deze activiteit niet (meer) uitschijven, maar wel je
    inschrijving overdragen naar een ander persoon.
</p>
@if ($enrollment->price > 0)
<p class="font-bold">
    Je moet er zelf zorg voor dragen dat je het inschrijfgeld weer terug krijgt
    van deze persoon.
</p>
@endif

@component('mail::button', ['url' => $cancelUrl])
    Inschrijving overdragen
@endcomponent
@endif
@endcomponent
@endslot

@slot('greeting')
Alvast bedankt voor je medewerking, en veel plezier bij
_{{ $activity->name }}_.

Met vriendelijke groet,

Gumbo Millennium
@endslot

{{-- Subcopy --}}
@slot('subcopy')
<p>
    Je ontvangt deze mail omdat je bent ingeschreven op <a href="{{ \route('activity.show', compact('activity')) }}"
        target="_blank" rel="noopener">{{ $activity->name }}</a>.
</p>
<p>
    Je kan je voor dit soort updates niet afmelden.
</p>
@endslot
@endcomponent
