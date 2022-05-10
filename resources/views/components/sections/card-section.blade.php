@props([
  'title' => null,
  'subtitle' => null,
  'callToAction' => null,
  'foreground' => 'bg-white',
  'background' => 'gray',
  'spacious' => true,
])
<x-container :space="$spacious ? 'large' : 'normal'" :background="$background" container-class="relative">
  {{-- Background --}}
  <x-slot name="before">
    <div class="absolute inset-0">
      <div class="{{ $foreground }} h-1/3 sm:h-1/2"></div>
    </div>
  </x-slot>

  <div class="relative max-w-7xl mx-auto">
    {{-- Title and subtitle --}}
    @if ($title)
    <div class="text-center">
      <h2 class="text-3xl tracking-tight font-extrabold text-gray-900 sm:text-4xl">
        {{ $title }}
      </h2>

      @if ($subtitle)
      <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
        {{ $subtitle }}
      </p>
      @endif
    </div>
    @endif

    {{-- Cards --}}
    <x-card-grid>
      {{ $slot }}
    </x-card-grid>

    {{-- Optional call to action --}}
    @if ($callToAction)
    <div class="mt-10 sm:flex sm:justify-center">
      {{ $callToAction }}
    </div>
    @endif
  </div>
</x-container>
