@props([
  'href',
  'subtitle' => null,
  'icon' => null,
])
<a href="{{ $href }}" class="-m-3 p-3 flex items-start rounded-lg hover:bg-gray-50">
  @if ($icon)
  <div class="mr-4">
    <x-icon :icon="$icon" class="w-5 h-5 text-gray-400 group-hover:text-gray-500" />
  </div>
  @endif

  <div>
    <p class="text-base font-medium text-gray-900">{{ $slot }}</p>
    @if ($subtitle)
    <p class="mt-1 text-sm text-gray-500">
      {{ $subtitle }}
    </p>
    @endif
  </div>
</a>
