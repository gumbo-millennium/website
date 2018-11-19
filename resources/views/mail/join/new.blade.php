@component('mail::message')
# Welkom bij Gumbo Millennium

Beste {{ $joinData['first_name'] }},

Bedankt voor je aanmelding bij Gumbo Millennium. Je aanmelding tot
lidmaatschap is doorgestuurd naar het bestuur en zij nemen
zo snel mogelijk contact met je op.

Dit is de informatie die je hebt ingestuurd:

@include('mail.join.data')

Klopt een van deze gegevens niet? Stuur dan een mailtje naar <a href="mailto:bestuur@gumbo-millennium.nl">bestuur@gumbo-millennium.nl</a>
om ze aan te passen.

Hopelijk tot snel!

Met vriendelijke groet,<br>
Gumbo Millennium
@endcomponent
