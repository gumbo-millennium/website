@props([
  'photo',
  'readOnly' => false,
])
<div class="rounded shadow group">
  <picture class="block">
    @if ($url = image_asset($photo->path)->preset('tile')->jpg())
      <source srcset="{{ (clone $url)->webp()->dpr(2) }} 2x, {{ (clone $url)->webp() }}" type="image/webp" />
      <img src="{{ $url }}" class="h-[400px] w-full object-cover rounded" />
    @else
      <x-empty-state.image class="h-64 w-full rounded-t" />
    @endif
  </picture>

  <div class="p-2 flex items-center">
    <div class="text-sm flex-grow">
      <p>
        {{ $photo->description }}
      </p>
      <p class="text-xs">
        Door <strong>{{ $photo->user?->name ?? 'onbekend' }}</strong>
      </p>
    </div>

    @if(! $readOnly)
    <div class="flex items-center flex-row gap-2">
      @canany(['edit'], $photo)
      <a href="{{ route('gallery.album.edit', $photo->album) }}" class="inline-block py-1 px-2 rounded border">
        <span class="text-sm">Bewerk album</span>
      </a>
      @elsecanany(['like', 'report'], $photo)
      <!--
      <div class="inline-block text-center py-1 px-2 rounded border">
        <x-icon icon="solid/fist-raised" class="h-4 mr-2" />
        <span class="text-sm">Respect</span>
      </div>

      <a href="{{ route('gallery.photo.report', $photo) }}" class="inline-block text-center py-1 px-2 rounded border">
        <x-icon icon="solid/skull-crossbones" class="h-4 mr-2" />
        <span class="text-sm">Niet ok</span>
      </a>
      -->
      @endcanany
    </div>
    @endif
  </div>
</div>
