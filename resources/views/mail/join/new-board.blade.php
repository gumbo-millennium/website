@component('mail::message')
# Nieuwe aanmelding

Geacht bestuur,

Er is een aanmelding tot lidmaatschap binnengekomen voor {{ $joinData['first_name'] }}.
De gegevens staan hieronder.

@include('mail.join.data')

Thanks,<br>
{{ config('app.name') }}
@endcomponent
