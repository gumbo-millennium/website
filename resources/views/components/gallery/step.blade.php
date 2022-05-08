@props([
  'number',
  'title',
])
<div class="py-8">
  <div class="flex flex-row items-center mb-2">
    <div
      class="flex-none mr-4 w-12 h-12 rounded-full flex items-center justify-center text-3xl font-title text-medium text-gray-800 bg-gray-100">
      {{ $number }}
    </div>
    <h3 class="font-title text-3xl">{{ $title }}</h3>
  </div>
  <div class="flex flex-row">
    <div class="w-12 mr-4">&nbsp;</div>
    <div class="flex-grow">
      {{ $slot }}
    </div>
  </div>
</div>
