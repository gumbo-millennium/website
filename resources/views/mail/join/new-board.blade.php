@component('mail::message')
# Nieuwe aanmelding

Geacht bestuur,

Er is een aanmelding tot lidmaatschap binnengekomen voor {{ $user->name }}.
De gegevens staan hieronder.

@include('mail.join.data')

Klik op onderstaande knop om de aanmelding af te handelen via de website.
<strong>Let op:</strong> de website heeft géén toegang tot het ledensysteem,
je kan via de site alleen snel de lidmaatschapsstatus van {{ $user->name }}
beheren.

@component('mail::button', ['url' => route('admin.join.view', ['request' => $request])])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
