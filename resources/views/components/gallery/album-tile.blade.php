@props([
  'album',
])
<div class="rounded shadow">
  <picture>
    @if ($url = image_asset($album->cover_image)->preset('tile')->height(256)->width(224))
      <source srcset="{{ (clone $url)->webp()->dpr(2) }} 2x, {{ (clone $url)->webp() }}" type="image/webp" />
      <img src="{{ $url }}" class="h-64 w-full object-cover rounded-t" />
    @else
    <x-empty-image class="h-64 w-full rounded-t" />
    @endif
  </picture>

  <div class="p-4">
    <h2 class="mb-4">
      <a class="font-title text-xl text-black" href="{{ route('gallery.album', $album) }}">{{ $album->name }}</a>
    </h2>
  </div>
</div>
