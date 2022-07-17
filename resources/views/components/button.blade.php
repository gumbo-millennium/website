@props([
    'href' => null,
    'type' => 'link',
    'size' => 'large',
    'color' => 'default',
])
@php
$color = match($color) {
  // 'dark' => 'text-white border-transparent bg-brand-500 hover:bg-brand-600',
  'primary' => 'text-white border-transparent bg-brand-600 hover:bg-brand-700',
  'outline' => 'text-brand-600 border-brand-500 bg-transparent hover:bg-brand-50 hover:border-brand-600',
  'danger' => 'text-gray-900 border-red-500 bg-transparent hover:bg-red-50 hover:border-red-600',
  'white' => 'text-brand-600 border-transparent bg-white hover:bg-brand-50',
  default => 'text-gray-900 border-gray-500 bg-transparent hover:bg-gray-50 hover:border-gray-600',
};
$size = match($size) {
  'large' => 'w-full px-8 py-3 md:py-4 md:text-lg md:px-10',
  'small' => 'w-auto px-4 py-2',
};
$attributes = $attributes->merge([
  'class' => "flex items-center justify-center {$size} border text-base font-medium rounded-md {$color}",
]);
@endphp
<div class="rounded-md shadow grid">
  @if ($type === 'link')
  <a {{ $attributes->merge(['href' => $href]) }}>{{ $slot }}</a>
  @else
  <button {{ $attributes->merge(['type' => $type]) }}>{{ $slot }}</button>
  @endif
</div>
