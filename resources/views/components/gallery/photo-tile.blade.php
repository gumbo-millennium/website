@props([
  'photo',
  'readOnly' => false,
])
<div class="rounded shadow group">
  @if ($url = image_asset($photo->path)->preset('tile')->webp())
    <img srcset="{{ (clone $url)->dpr(2) }} 2x, {{ $url }}" class="block h-[400px] w-full object-cover rounded" loading="lazy" />
  @else
    <x-empty-state.image class="h-64 w-full rounded-t" />
  @endif

  <div class="p-2 flex items-center">
    <div class="text-sm flex-grow">
      <p>
        {{ $photo->description }}
      </p>
      <p class="text-xs">
        Door <strong>{{ $photo->user?->name ?? 'onbekend' }}</strong>
        @if($photo->taken_at)
          op <time datetime="{{ $photo->taken_at->toIso8601String() }}">{{ $photo->taken_at?->isoFormat('d MMM YYYY') }}</time>
        @endif
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
        <x-icon icon="solid/hand-fist" class="h-4 mr-2" />
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
