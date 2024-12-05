@props([
  'icon',
  'title',
  'label' => null,
])

<div class="py-4 flex items-center gap-x-6">
  <div class="flex-none w-8 text-center">
    <x-icon class="h-8 text-brand-50" :icon="$icon"/>
  </div>
  <div class="flex-grow flex flex-col items-start text-white">
    <p class="font-bold">{{ $title }}</p>
    @if($label)
      <p class="text-sm">{{ $label }}</p>
    @endif
  </div>
</div>
