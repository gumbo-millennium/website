@props([
  'subtitle' => null
])
<div class="container py-8">
    <div class="font-title text-5xl font-medium text-black">{{ $slot }}</div>

    @if ($subtitle !== null)
      <div class="mt-2">
        {{ $subtitle }}
      </div>
    @endif
</div>
