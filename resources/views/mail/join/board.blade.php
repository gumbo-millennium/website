@component('mail::message')
# Aanmelding nieuw lid

Geacht bestuur,

Er is een aanmelding binnengekomen voor een nieuw lid van Gumbo Millennium.

@if ($submission->gender === 'man')
Zijn naam is {{ $submission->name }}.
@elseif ($submission->gender === 'vrouw')
Haar naam is {{ $submission->name }}.
@else
Zijn/haar naam is {{ $submission->name }}.
@endif

Verdere gegevens zijn, ter waarboring van de privacy van het lid,
te vinden in het administratiepaneel.

@component('mail::button', ['url' => $actionUrl])
Bekijk aanmelding
@endcomponent

Met vriendelijke groet,

De Digitale Commissie
@endcomponent
