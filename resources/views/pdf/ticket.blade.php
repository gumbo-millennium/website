<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=210mm, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Ticket voor {{ $activity->name }}</title>

  @if ($showWeb ?? false)
  <link rel="stylesheet" href="{{ mix('app.css') }}">
  @else
  <style>
    {{ mix_file('mail.css') }}
  </style>
  @endif
</head>
<body class="p-8 bg-gray-200 min-h-screen">
  <div class="container flex">
      <x-pdf.ticket-box>
        <div class="absolute inset-y-3 right-0 border-r border-gray-400 border-dashed" role="none"></div>

        <img src="{{ mix('images/logo-text-green.svg') }}" class="h-16 mb-4" alt="Logo Gumbo Millennium" />

        <div class="mb-8 space-y-4">
          <h1 class="font-title font-bold text-4xl">{{ $activity->name }}</h1>
          <h2 class="font-title font-bold text-3xl text-gray-800">{{ $ticket->title }}</h2>
        </div>

        <h3 class="font-bold text-2xl uppercase">{{ $subject->name }}</h3>
      </div>
      <div class="flex-none rounded-xl bg-white p-8 text-center">
        <div class="mb-4">
          <img src="{{ Enroll::getTicketQrCode($enrollment) }}" alt="QR Code" height="200" width="200">
        </div>

        <data class="font-bold block uppercase font-mono">{{ $enrollment->enrollment_code }}</data>
      </x-pdf.ticket-box>
    </div>

    <div class="container mt-16 w-full flex h-full">
      <div class="w-7/12 h-full pr-8">
        <x-pdf.ticket-box class="w-full">
          {{ $activity->ticket_html }}
        </x-pdf.ticket-box>
      </div>
      <div class="w-5/12 space-y-8">
        <x-pdf.ticket-box class="bg-brand-600 text-white">
          <x-slot name="title">Persoonsgebonden ticket</x-slot>

          <p class="text-lg">Dit ticket is persoonsgebonden en niet overdraagbaar.</p>
          <p>Wil je het overdragen? Ga naar gumbo.nu/ticket-overdragen</p>
        </x-pdf.ticket-box>

        <x-pdf.ticket-box>
          <x-slot name="title">Locatie</x-slot>

          @if (URL::isValidUrl($activity->location_address))
            <p>
              <strong class="block font-bold text-lg">{{ $activity->location }}</strong>
            </p>
            <p>
              Bekijk online:
              <a href="{{ $activity->location_address }}" target="_blank" class="underline">
                {{ parse_url($activity->location_address, PHP_URL_HOST) }}
              </a>
            </p>
          @else
          <address>
            <strong class="block font-bold text-lg">{{ $activity->location }}</strong>
            {{ $activity->location_address }}
          </address>
          @endif
        </x-pdf.ticket-box>
      </div>
    </div>
  </div>
</body>
</html>
