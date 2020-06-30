@php
$contact = collect([
    $submission->email,
    $submission->phone
])->reject(fn ($value) => empty($value))->implode(' of ');
@endphp

@component('mail::message')
{{-- Greeting --}}
@slot('header')
Welkom bij Gumbo Millennium!
@endslot

{{-- Image --}}
@slot('mailImage', mix('images/mail/header-welcome.png'))

Beste {{ $submission->first_name }},

Bedankt voor je aanmelding voor Gumbo Millennium.

@if(!empty($contact))
Het bestuur zal zo snel mogelijk contact met je opnemen via {{ $contact }}.
@else
Het bestuur zal zo snel mogelijk contact met je opnemen.
@endif

Leuk dat je er bij bent, en hopelijk tot snel!

Met vriendelijke groet,

Gumbo Millennium

{{-- Subcopy --}}
@slot('subcopy')
Dit is een automatisch opgesteld bericht, reacties op deze mail worden niet bezorgd.

Heb je toch een brandende vraag, stuur dan een mailtje naar bestuur@gumbo-millennium.nl.
@endslot
@endcomponent
