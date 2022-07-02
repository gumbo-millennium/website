{{-- Title main --}}
<div class="mt-4 mb-12 md:flex md:items-center md:justify-between">
  {{-- Page title --}}
  <div class="flex-1 min-w-0">
    <h2 class="text-4xl font-bold font-title leading-relaxed text-gray-900 sm:text-5xl pb-2 lg:truncate">{{ $title }}</h2>

    @if ($subtitle)
    <p class="text-lg font-medium text-gray-500">{{ $subtitle }}</p>
    @endif
  </div>
</div>
