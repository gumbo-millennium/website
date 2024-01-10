<x-mail::message markdown>
  <x-slot:summary>
    Organisatorische info voor {{ $activity->name }}
  </x-slot:summary>

  <h1>{{ $activity->name }} begint bijna.</h1>

  Nog een paar dagen en dan begint {{ $activity->name }}. Hopelijk heb je er al zin in.

  In dit mailtje nog wat last-minute info voor jou als organisatie.

  ## Deelnemers en tickets

  Er zijn {{ $ticketCount }} tickets verkocht (of plekken gereserveerd). Deze wil je vast op de een of andere manier controleren.

  ## Tickets scannen

  De makkelijkste aanpak is natuurlijk met een ticktscanner. Deze kan je vinden op [gumbo.nu/scanner](https://gumbo.nu/scanner).

  Kies in de scanner-app voor de "{{ $activity->name }}" activiteit, en je kan los.

  1. Groen scherm: geldig ticket.
  2. Rood scherm: ongeldig ticket.
  3. Geel scherm: ticket is al gescand, maar wel geldig.

  Je moet ingelogd zijn om de scanner te gebruiken. De scanner kan gebruikt worden door jou,
  het bestuur en door de leden van de groep "{{ $activity->role?->name ?? "(geen, raar...)" }}".

  {{-- Make sure button isn't prose --}}
  </div>
  <x-mail::button :url="route('barcode.show', $activity)">
  Open Scanner
  </x-mail::button>
  <div class="prose">

  ### Deelnemerlijsten

  Een alternatieve aanpak is door gebruik te maken van presentielijsten. Deze kan je downloaden via het admin panel:

  1. Ga naar [je activiteit in het admin panel]({{ secure_url("/admin/resources/activities/{$activity->id}") }}).
  2. Klik rechtsbovenin op de drie puntjes.
  3. Klik op "Exporteer gegevens".
  4. Kies "Presentielijst" en klik op "Deelnemers exporteren".

  Mocht je de volledige vragenlijst nodig hebben, kies dan in plaats van "Presentielijst" voor "Alle gegevens". Let op dat hiervan een
  auditlog wordt bijgehouden, voor AVG-doeleinden. Laat deze lijst ook niet rondslingeren.

  ## Technische vragen?

  Je kan altijd de DC om hulp vragen. Dit kan het makkelijkste door ze een berichtje te sturen via Telegram,
  of alternatief via de mail.

  Dikke prima, toch?

  Kusje van je favoriete commissie,

  De DC
</x-mail::message>
