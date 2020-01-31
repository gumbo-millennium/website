@component('mail::message')
# Betaalbevestinging voor {{ $activity->name }}

Hoi {{ $user->first_name }},

Bedankt voor je betaling. Je inschrijving voor {{ $activity->name }} is nu bevestigd.


The body of your message.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
