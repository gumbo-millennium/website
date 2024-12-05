<?php
$cost = $enrollment->total_price;
$trxCost = null;
if ($cost != null) {
    $trxCost = sprintf('Incl. %s transactiekosten', Str::price($enrollment->total_price - $enrollment->price));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=210mm, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <title>Ticket voor {{ $activity->name }}</title>

  @vite('resources/css/app.css')
</head>
<body class="bg-white min-h-screen">
<main>
  <div class="bg-brand-600 space-y-8 p-8">
    <div class="mx-16 flex items-center">
      <img src="{{ Vite::image('images/logo-text-white.svg') }}" class="h-16" alt="Logo Gumbo Millennium"/>
    </div>

    <div class="mx-16">
      <div class="grid grid-cols-2">
        <div>
          <div class="mb-8 space-y-4">
            <h1 class="font-title text-3xl text-white">{{ $activity->name }}</h1>
          </div>

          <x-pdf.stat
            icon="solid/ticket"
            :title="$ticket->title" />

          <x-pdf.stat
            icon="solid/user"
            :title="$subject->name"
            :label="$subject->email" />

          <x-pdf.stat
            icon="solid/coins"
            :title="Str::price($cost) ?? 'Gratis'"
            :label="$trxCost" />
        </div>

        <div class="flex items-center justify-end">
          <div class="flex-none rounded-lg bg-white p-8 text-center">
            <div class="mb-4">
              @if ($enrollment->has2dBarcode())
                <img src="{{ Enroll::getBarcodeImage($enrollment, 80 * 3) }}" alt="Barcode" height="80" class="max-w-full">
              @else
                <img src="{{ Enroll::getBarcodeImage($enrollment, 200 * 3) }}" alt="Barcode" height="200" width="200">
              @endif
            </div>

            <data class="text-black font-bold block uppercase font-mono leading-none">
              {{ $enrollment->barcode }}
            </data>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mx-16 mt-16">
    <div class="grid grid-cols-pdf gap-8">

      <div class="min-h-32 space-y-8">
        <div>
          <h2 class="font-title text-lg text-brand-800 mb-4">Omschrijving</h2>

          @if ($activity->ticket_html)
            {{ $activity->ticket_html }}
          @else
            <p class="text-gray-700 font-italic">
              Deze activiteit heeft geen informatie.
            </p>
          @endif
        </div>

        <?php
        $props = collect()
            ->add(['Naam', $subject->name])
            ->add(['E-mailadres', $subject->email])
            ->concat(collect($enrollment->form)->map(fn ($x, $y) => [$y, $x]));
?>
        <div>
          <h2 class="font-title text-lg text-brand-800 mb-4">Gegevens aanmelding</h2>

          <dl class="grid">
            @foreach($props as [$key, $value])
              <dt class="text-sm">{{ $key }}</dt>
              <dd class="ml-4 mb-4">{{ $value }}</dd>
            @endforeach
          </dl>
        </div>
      </div>

      <div class="space-y-8">
        <x-pdf.box title="Persoonsgebonden ticket" icon="solid/user-lock">
          <p>Dit ticket is persoonsgebonden.</p>
          <p class="text-sm">Wil je het overdragen? Ga naar <a href="https://gumbo.nu/ticket-overdragen">gumbo.nu/ticket-overdragen</a>.</p>
        </x-pdf.box>

        <x-pdf.box title="Aanvang" icon="solid/clock">
          <p>
            {{ $activity->start_date->isoFormat('DD-MM-YYYY') }}
            om {{ $activity->start_date->isoFormat('HH:mm') }}.
          </p>
          <p class="text-sm text-gray-700">
            @if ($activity->start_date->diffInDays($activity->end_date) > 1)
              Verwachte einddatum: {{ $activity->end_date->isoFormat('D MMM, HH:mm') }}
            @else
              Verwachte eindtijd: {{ $activity->end_date->isoFormat('HH:mm') }}
            @endif
          </p>
        </x-pdf.box>

        <x-pdf.box title="Locatie" icon="solid/location-pin">
          @if (URL::isValidUrl($activity->location_address))
            <h4 class="block font-bold text-lg">{{ $activity->location }}</h4>
            <p>
              Bekijk online:
              <a href="{{ $activity->location_address }}" target="_blank" class="underline">
                {{ parse_url($activity->location_address, PHP_URL_HOST) }}
              </a>
            </p>
          @else
            <h4 class="block font-bold text-lg">{{ $activity->location }}</h4>
            <p>{{ $activity->location_address }}</p>
          @endif
        </x-pdf.box>
      </div>
    </div>
  </div>
</main>
</body>
</html>
