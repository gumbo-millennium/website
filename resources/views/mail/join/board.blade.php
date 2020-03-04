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

Meer informatie vind je hieronder. Je kan het item ook bekijken via het admin panel.

@component('mail::button', ['url' => $adminRoute])
Bekijk aanmelding op de site
@endcomponent

## Gegevens aanmelding

@component('mail::table')
|      Veld      |    Waarde    |
|----------------|--------------|
@foreach ($submission->toArray() as $key => $value)
| **{{ $key }}** | {{ $value }} |
@endforeach
@endcomponent

------

Met vriendelijke groet,

De Digitale Commissie
@endcomponent
