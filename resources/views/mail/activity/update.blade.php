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
    Je ontvangt deze mail omdat je bent ingeschreven op <a href="{{ \route('activity.show', compact('activity')) }}"
        target="_blank" rel="noopener">{{ $activity->name }}</a> bij Gumbo Millennium.
</p>

<p>
    @if ($cancelType === 'cancel')
        Indien je niet meer deel wilt nemen aan deze activiteit, dan kan je jezelf <a href="{{ $cancelUrl }}">uitschrijven via deze link.</a>
    @else
        Indien je niet meer deel wilt nemen aan deze activiteit, kan je je inschrijving <a href="{{ $cancelUrl }}">overdragen aan iemand anders</a>.
    @endif
</p>

<p>
    Werkt de link niet? Copy-paste dan deze URL:<br />
    {{ $cancelUrl }}
</p>

@endslot
@endcomponent
