@extends('enrollments.scanner.layout')

@section('content')
<h1 class="font-title text-3xl text-white font-bold mb-4">
  Ticket Scanner
</h1>

<div class="grid grid-cols-1 gap-4">
  @foreach ($activities as $activity)
  <div
    class="relative rounded-lg border border-gray-700 bg-gray-800 px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-500 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-brand-700">
    <div class="flex-shrink-0">
      @if ($activity->poster)
          <img class="h-10 w-10 rounded-full" src="{{ image_asset($activity->poster)->square(256)->fit('crop')->jpg() }}"
            alt="Foto van {{ $activity->name }}">
      @else
          <div class="h-10 w-10 rounded-full bg-gray-700"></div>
      @endif
    </div>
    <div class="flex-1 min-w-0">
      <a href="{{ route('barcode.show', $activity) }}" class="focus:outline-none">
        <span class="absolute inset-0" aria-hidden="true"></span>
        <p class="font-medium text-gray-200">{{ $activity->name }}</p>
        <p class="text-sm text-gray-400 truncate">{{ $activity->start_date->isoFormat('dddd D MMMM, HH:mm') }}</p>
      </a>
    </div>
  </div>
  @endforeach
</div>
@endsection
