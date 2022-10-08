@props([
  'narrow' => false,
])
<div {{ $attributes->class([
  "mt-12 max-w-lg mx-auto grid grid-cols-1 gap-5" => true,
  'lg:grid-cols-3 lg:max-w-none' => $narrow === false,
  'lg:grid-cols-2 lg:max-w-none' => $narrow === true,
]) }}>
  {{ $slot }}
</div>
