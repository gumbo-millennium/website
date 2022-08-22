@extends('enrollments.scanner.layout')

@section('content')
<div class="flex-grow flex flex-col items-center w-full" data-content="scanner" data-index-url="{{ route('barcode.index') }}" data-preload-url="{{ route('barcode.preload', $activity) }}" data-consume-url="{{ route('barcode.consume', $activity) }}" data-csrf-token="{{ csrf_token() }}">
  <div class="relative z-10 w-full flex flex-row">
    <div class="flex-grow">
      {{-- Page title --}}
      <h1 class="font-title text-3xl text-white font-bold mb-4">
        Ticket Scanner
      </h1>

      <h2 class="mb-8 text-gray-200 font-title text-lg">
        Scannen voor {{ $activity->name }}
      </h2>
    </div>

    <div class="flex-none">
      <button class="rounded-full h-16 w-16 flex items-center justify-center bg-gray-200 text-brand-700" data-action="fullscreen">
        <x-icon icon="solid/expand" class="h-8 w-8" data-icon="open" />
        <x-icon icon="solid/compress" class="h-8 w-8" data-icon="close" />
        <x-icon icon="solid/triangle-exclamation" class="h-8 w-8 text-orange-700" data-icon="error" />
      </button>
    </div>
  </div>

  {{-- Screen filler --}}
  <div class="min-w-0 flex-grow"></div>

  {{-- Loader --}}
  <div class="rounded px-6 py-4 bg-gray-800 text-gray-200" data-content="loading">
    <div class="flex items-center gap-4">
      <x-icon class="h-8 animate-spin" icon="solid/spinner" />
      <h3 class="font-title font-bold text-2xl text-white" data-content="loading-reason">Laden...</h3>
    </div>
  </div>

  {{-- Results --}}
  <div class="hidden bg-gray-800 border-gray-700 rounded py-4 px-6" data-content="result">
    <div class="flex-row items-center gap-4" data-result="valid">
      <x-icon class="h-8 text-white" icon="solid/check" />
      <h3 class="font-title font-bold text-2xl text-white">Geldig ticket</h3>
    </div>

    <div class="flex-row items-center gap-4" data-result="consumed">
      <x-icon class="h-8 text-white" icon="solid/triangle-exclamation" />
      <h3 class="font-title font-bold text-2xl text-white">Ticket al gebruikt</h3>
    </div>

    <div class="flex-row items-center gap-4" data-result="invalid">
      <x-icon class="h-8 text-white" icon="solid/xmark" />
      <h3 class="font-title font-bold text-2xl text-white">Ticket ongeldig</h3>
    </div>
  </div>

  {{-- Barcode --}}
  <div class="flex items-center my-4">
    <div class="text-xl font-monospace text-gray-700 bg-white px-4 py-2 mx-auto rounded-lg text-center hidden" data-content="barcode"></div>
  </div>

  {{-- Safety margin (for mobile UIs) --}}
  <div class="w-full h-[32px]"></div>
</div>
@endsection
