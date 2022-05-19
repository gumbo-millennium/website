@component('mail::message')

@slot('summary')
Laatste informatie voor {{ $activity->name }}
@endslot

{{-- Image --}}
@slot('mailImage', mix('images-mail/header-update.png'))
@slot('html')
<p class="lead">Beste {{ $participant->first_name }},</p>

<p>De organisatie van {{ $activity->name }} wil je graag het volgende bericht sturen.</p>

<hr />

<h2>{{ $userTitle }}</h2>

{{ $userBody }}

<hr />
@endslot

@slot('greeting')
Veel plezier bij _{{ $activity->name }}_.

Met vriendelijke groet,

Gumbo Millennium
@endslot

{{-- Subcopy --}}
@slot('subcopy')
<p>
    Je ontvangt deze mail omdat je bent ingeschreven op <a href="{{ route('activity.show', $activity) }}"
        target="_blank" rel="noopener">{{ $activity->name }}</a> bij Gumbo Millennium.
</p>

<p>
    Wil je iets wijzigen aan je inschrijving, of je inschrijving annuleren of overdagen? <a href="{{ $enrollmentUrl }}">klik dan op deze link.</a>
</p>

<p>
    Werkt de link niet? Copy-paste dan deze URL:<br />
    {{ $enrollmentUrl }}
</p>

@endslot
@endcomponent
