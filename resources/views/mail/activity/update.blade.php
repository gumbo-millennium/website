@component('mail::message')

@slot('summary')
Update vanuit de organisatie van {{ $activity->name }}
@endslot

{{-- Image --}}
@slot('mailImage', mix('images-mail/header-update.png'))
@slot('html')
<h2>{{ $userTitle }}</h2>

{{ $userBody }}

<div class="m-4 mt-8 py-4 px-8 bg-gray-100 text-gray-700 text-center">
    <p>
        Dit is een bericht verstuurd door de organisatie van {{ $activity->name }}.<br />
        Wil je je inschrijving beheren? <a href="{{ $enrollmentUrl }}">klik dan hier</a>.
    </p>
    <p class="text-xs text-gray-600 mt-2">
        Indien dit bericht ongepast is, kan je het doorsturen naar bestuur@gumbo-millennium.nl,
        zij kunnen de afzender hierop aanspreken.
    </p>
</div>
@endslot

@slot('greeting', '')

{{-- Subcopy --}}
@slot('subcopy')
<p>
    Werkt de link om je inschrijving te beheren niet? Copy-paste dan deze URL:<br />
    {{ $enrollmentUrl }}
</p>
@endslot
@endcomponent
