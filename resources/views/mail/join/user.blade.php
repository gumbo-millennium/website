@component('mail::message')
# Welkom bij Gumbo Millennium!

Beste {{ $submission->first_name }},

Bedankt voor je aanmelding voor Gumbo Millennium.

@if (!empty($submission->email) && !empty($submission->phone))
Het bestuur zal z.s.m. contact met je opnemen via {{ $submission->email }} of {{ $submission->phone }}.
@else
Het bestuur zal z.s.m. contact met je opnemen via {{ $submission->email }}.
@endif

Leuk dat je er bij bent, en hopelijk tot snel!

Met vriendelijke groet,

Gumbo Millennium
@endcomponent
