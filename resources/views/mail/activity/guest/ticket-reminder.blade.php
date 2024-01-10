<x-mail::message markdown>
<h1>Je ticket voor {{ $activity->name }}</h1>

Het is bijna zover, over ongeveer twee uur begint {{ $activity->name }}. Hopelijk heb je er al zin in.

Om het voor de organisatie lekker makkelijk te maken, kan je via onderstaande knop snel bij je ticket komen.

Verder staat hieronder nog een keer de “mand” (aanvang en locatie). Mocht je nog vragen hebben, dan kan je die lekker vragen aan de organisatie.

## Mand

Aanvang: {{ $activity->start_date->isoFormat('dddd D MMMM YYYY, HH:mm') }}<br>
Locatie: {{ $activity->location }}<br />
Ticket: {{ $ticket->title }} t.w.v. {{ Str::price($ticket->price) }}

{{-- Make sure button isn't prose --}}
</div>
<x-mail::button :url="route('account.tickets')">
Bekijk je tickets
</x-mail::button>
<div class="prose">

Veel plezier bij {{ $activity->name }},<br>
Gumbo Millennium
</x-mail::message>
