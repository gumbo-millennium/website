<?php
$contact = collect([
    $submission->email,
    $submission->phone
])->reject(fn ($value) => empty($value))->implode(' of ');
?>

<x-mail::message :mail-image="mix('images/header-welcome.png')">
{{-- Greeting --}}
<x-slot name="header">
    Welkom bij Gumbo Millennium!
</x-slot>

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
<x-slot name="subcopy">
    Dit is een automatisch opgesteld bericht, reacties op deze mail worden niet bezorgd.

    Heb je toch een brandende vraag, stuur dan een mailtje naar bestuur@gumbo-millennium.nl.
</x-slot>
</x-mail::message>
