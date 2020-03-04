@component('mail::message')
# Welkom bij Gumbo Millennium!

Beste {{ $submission->first_name }},

Bedankt voor je aanmelding voor Gumbo Millennium.

@if (!empty($submission->email) && !empty($submission->phone))
Het bestuur zal z.s.m. contact met je opnemen via {{ $submission->email }} of {{ $submission->phone }}.
@else
Het bestuur zal z.s.m. contact met je opnemen via {{ $submission->email }}.
@endif

Bedankt voor je aanmelding, en hopelijk tot snel!

Met vriendelijke groet,

Gumbo Millennium
@endcomponent
