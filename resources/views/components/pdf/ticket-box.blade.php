@props([
  'title' => null,
])
<div {{ $attributes->merge(['class' => "w-full p-8 bg-white rounded-xl relative"]) }}>
  @if ($title)
      <h2 class="font-title text-3xl font-light">{{ $title }}</h2>
  @endif

  {{ $slot }}
</div>
