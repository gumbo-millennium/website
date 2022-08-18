@extends('enrollments.scanner.layout')

@section('content')
<h1 class="font-title text-3xl text-white font-bold mb-4">
  Ticket Scanner
</h1>

<h2 class="mb-8 text-gray-200 font-title text-lg">
  Scannen voor {{ $activity->name }}
</h2>

<div data-content="scanner" data-preload-url="{{ route('barcode.preload', $activity) }}" data-consume-url="{{ route('barcode.consume', $activity) }}" data-csrf-token="{{ csrf_token() }}">
  <video class="w-full"></video>

  <div class="flex items-center my-4">
    <div class="text-xl font-monospace text-gray-700 bg-white px-4 py-2 mx-auto rounded-lg text-center hidden" data-content="barcode"></div>
  </div>

  <div class="absolute inset-0 opacity-70 bg-gray-700" data-content="loading">
    <div class="mt-32 flex flex-col items-center gap-8">
      <x-icon class="h-24 w-24 text-white animate-spin" icon="solid/spinner" />

      <h3 class="font-title font-bold text-5xl text-white">Laden...</h3>

      <p class="text-3xl text-gray-300" data-content="loading-reason">
        Applicatie wordt geladen...
      </p>
    </div>
  </div>

  <div class="hidden bg-gray-800 border-gray-700 rounded p-4 px-6" data-content="result">
    <div class="max-w-2xl mx-auto text-center">
        <div class="flex flex-col items-center gap-4" data-result="valid">
          <x-icon class="h-16 text-white" icon="solid/check" />

          <h3 class="font-title font-bold text-3xl text-white">Geldig ticket</h3>

          <p class="text-2xl text-gray-300">
            Dit ticket is geldig!
          </p>
        </div>

        <div class="flex flex-col items-center gap-4" data-result="consumed">
          <x-icon class="h-16 text-white" icon="solid/triangle-exclamation" />

          <h3 class="font-title font-bold text-3xl text-white">Ticket al gebruikt</h3>

          <p class="text-2xl text-gray-300">
            Dit ticket is al een keer gescand.
          </p>
        </div>
        <div class="flex flex-col items-center gap-4" data-result="invalid">
          <x-icon class="h-16 text-white" icon="solid/xmark" />

          <h3 class="font-title font-bold text-3xl text-white">Ticket ongeldig</h3>

          <p class="text-2xl text-gray-300">
            Dit ticket is niet geldig. Mogelijk is het voor een andere
            activiteit, geannuleerd of doorgegeven.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
