@props([
  'title',
  'icon' => null
])

<div class="p-8 bg-gray-100 rounded-lg relative overflow-hidden">
  @if($icon)
    <div class="absolute -bottom-4 -right-4 w-32 flex text-left">
      <x-icon class="h-32 max-w-32 text-gray-200" :icon="$icon"/>
    </div>
  @endif

  <div class="relative">
    <h3 class="font-title text-lg text-brand-800 mb-4">{{ $title }}</h3>

    <div class="text-black">
      {{ $slot }}
    </div>
  </div>
</div>
