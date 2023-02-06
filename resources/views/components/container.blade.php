@props([
  'background' => null,
  'space' => 'normal',
  'containerClass' => null,
  'wide' => false,
])
<?php
$containerPadding = match ($space) {
    'large' => 'pt-16 pb-20 lg:pt-24 lg:pb-28',
    'small' => 'pt-8 pb-12 lg:pt-12 lg:pb-16',
    'tiny' => 'pt-4 pb-8 lg:pt-8 lg:pb-12',
    'none' => 'py-0',
    default => 'pt-16 pb-20 lg:pt-24 lg:pb-28'
};
$background = match ($background) {
    'brand' => 'bg-brand-50',
    'brand-gray-900' => 'bg-brand-600 text-white',
    'gray' => 'bg-gray-50',
    'dark' => 'bg-gray-900 text-white',
    'white' => 'bg-white',

    // Allow css values
    default => $background,
};
$containerWidth = $wide ? 'max-w-full' : 'max-w-7xl';
?>
<section class="{{ trim("{$background} {$containerClass}") }}">
  {{-- Allow magic before elements --}}
  {{ $before ?? null }}

  {{-- Content, inset --}}
  <div {{ $attributes->class("{$containerWidth} mx-auto px-4 sm:px-6 lg:px-8 {$containerPadding}") }}>
    {{ $slot }}
  </div>
</section>
