@component('mail::message')
# Welkom bij Gumbo Millennium!

Beste {{ $submission->first_name }},

Bedankt voor je aanmelding voor Gumbo Millennium.

@if (!empty($submission->email) && !empty($submission->phone))
Het bestuur zal z.s.m. contact met je opnemen via {{ $submission->email }} of {{ $submission->phone }}.
@else
Het bestuur zal z.s.m. contact met je opnemen via {{ $submission->email }}.
@endif

Tevens is er automatisch een account voor je aangemaakt. Hierover ontvang je straks automatisch een bericht.

Bedankt voor je aanmelding, en hopelijk tot snel!

Met vriendelijke groet,

Gumbo Millennium
@endcomponent
