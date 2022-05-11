@props([
  'icon' => null,
  'title' => null,
])
<div {{ $attributes->class('block w-full p-12 text-center') }}>
  @if ($icon)
  <x-icon :icon="$icon" class="mx-auto h-12 w-12 text-gray-400 mb-2" role="none" aria-hidden="true" />
  @endif

  @if ($title)
    <h3 class="mb-1 text-lg font-medium text-gray-900">{{ $title }}</h3>
  @endif

  <p class="text-md text-gray-500">{{ $slot }}</p>
</div>
