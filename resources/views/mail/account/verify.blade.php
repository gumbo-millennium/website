@component('mail::message')
# Welkom bij Gumbo Millennium

Beste {{ $subject->first_name }},

Bedankt voor je aanmelding bij Gumbo Millennium. Om je inschrijving af te ronden, hoef je alleen maar je e-mailadres te bevestigen
door op onderstaande knop te drukken.

@component('mail::button', compact('url'))
Bevestig e-mailadres
@endcomponent

Het kan, na het bevestigen van je e-mailadres, even duren voordat je toegang hebt. We halen de lidmaatschappen pas op na verificatie van je e-mailadres.

Heb je geen account gemaakt bij Gumbo Millennium, dan hoef je niks te doen.
@endcomponent
