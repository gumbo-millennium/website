<div class="container container--lg py-4">
  <x-notice type="info" title="Activiteiten in je kalender?" class="mx-8">
    <p>
      Wil je de activiteiten waarvoor je bent ingeschreven direct in je agenda? Dat kan!<br />
      Voeg onderstaande URL toe aan je agenda.
    </p>

    <p class="p-4 border border-brand-400 rounded my-2">
      <a href="{{ $calendarUrl }}" class="text-brand-600">
        {{ $calendarUrl }}
      </a>
    </p>

    <p class="text-gray-700 text-sm">
      Gebruik je Google Calendar? <a href="{{ $googleUrl }}" rel="noopener noreferrer" target="_blank">voeg 'm dan snel toe</a>!
    </p>
  </x-notice>
</div>
