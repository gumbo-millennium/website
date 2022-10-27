@props([
  'album',
])
<div class="rounded shadow">
  @if ($url = image_asset($album->cover_image)->preset('tile')->height(256)->width(224)->webp())
    <img srcset="{{ (clone $url)->dpr(2) }} 2x, {{ $url }}" class="block h-64 w-full object-cover rounded-t" />
  @else
    <x-empty-state.image class="h-64 w-full rounded-t" />
  @endif

  <div class="p-4">
    <h2 class="mb-4">
      <a class="font-title text-xl text-black" href="{{ route('gallery.album', $album) }}">{{ $album->name }}</a>
    </h2>
  </div>
</div>
