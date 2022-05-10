@props([
  'href',
])
<a href="{{ $href }}" class="text-base font-medium text-gray-500 hover:text-gray-900">
  {{ $slot }}
</a>
