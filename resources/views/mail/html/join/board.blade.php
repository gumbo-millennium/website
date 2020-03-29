@php
$actionText = 'Bekijk aanmelding';
@endphp

@component('mail::message')
{{-- Greeting --}}
@slot('header')
Aanmelding nieuw lid
@endslot

{{-- Intro Lines --}}
Geacht bestuur,

Er is een aanmelding binnengekomen voor een nieuw lid van Gumbo Millennium.

Zijn/haar naam is {{ $submission->name }}.

Verdere gegevens zijn, ter waarboring van de privacy van het lid,
te vinden in het administratiepaneel.

{{-- Action Button --}}
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

{{-- Outro Lines --}}
<p class="text-gray-primary-1">
    Dit is een automatisch bericht vanuit de website, reageren is niet mogelijk.
</p>

{{-- Subcopy --}}
@slot('subcopy')
@lang(
    "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser: [:actionURL](:actionURL)',
    [
        'actionText' => $actionText,
        'actionURL' => $actionUrl,
    ]
)
@endslot
Met vriendelijke groet,

De Digitale Commissie
@endcomponent
