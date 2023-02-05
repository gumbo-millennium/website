<?php declare(strict_types=1);
use App\Enums\AlbumVisibility;

$activityName = $album->activity?->name;
$isPrivate = $album->visibility == AlbumVisibility::Private;

$stats = array_filter([
    $isPrivate
      ? ['icon' => 'solid/eye-slash', 'label' => 'Privé album']
      : ['icon' => 'solid/users', 'label' => 'Zichtbaar voor leden'],

    ['icon' => 'solid/calendar-days', 'label' => $activityName ? "Hoort bij {$activityName}" : null],
    ['icon' => 'solid/user', 'label' => $album->user?->public_name],
    ['icon' => 'solid/images', 'label' => trans_choice(':count photo|:count photos', $album->photos->count())],
    ['icon' => 'solid/pencil', 'label' => $album->updated_at->isoFormat('D MMM YYYY') ],
], fn ($row) => ! empty($row['label']));
?>
<x-page :title="[$album->name, 'Galerij']" hide-flash="true">
  <x-sections.header
    :title="$album->name"
    :crumbs="['/' => 'Home', '/gallery' => 'Galerij']"
    :stats="$stats"
    >
    <x-slot name="buttons">
      <div class="grid gap-4 w-full sm:contents">
        @can('update', $album)
        <form id="edit-album-visibility" action="{{ route('gallery.album.edit', $album) }}" method="POST" class="hidden">
          @csrf
          @method('PATCH')
        </form>
        <x-button color="" size="small" href="{{ route('gallery.album.edit', $album) }}" class="flex items-center">
          <x-icon icon="solid/pencil" class="h-4" />
          <span class="ml-2 md:sr-only">Bewerken</span>
        </x-button>

        @if ($isPrivate)
        <x-button type="submit" name="visibility" value="public" style="primary" size="small" form="edit-album-visibility"
          class="flex items-center">
          <x-icon icon="solid/eye" class="h-4" />
          <span class="ml-2 md:sr-only">Publiceren</span>
        </x-button>
        @else
        <x-button type="submit" name="visibility" value="private" color="" size="small" form="edit-album-visibility"
          class="flex items-center">
          <x-icon icon="solid/eye-slash" class="h-4" />
          <span class="ml-2 lg:sr-only">Verbergen</span>
        </x-button>
        @endif
        @endcan

        @can('upload', $album)
        <div class="col-start-1 col-end-3">
          <x-button style="primary" size="small" href="{{ route('gallery.album.upload', $album) }}" class="flex items-center">
            <x-icon icon="solid/upload" class="h-4 mr-2" />
            Uploaden
          </x-button>
        </div>
        @endcan
      </div>
    </x-slot>
  </x-sections.header>

<x-container>
  @if ($album->photos->isNotEmpty())
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach ($album->photos as $photo)
    <x-gallery.photo-tile :photo="$photo" />
    @endforeach
  </div>
  @else
  <x-empty-state.message title="Leeg album" icon="solid/images">
    Er zijn nog geen foto's in dit album
  </x-empty-state.message>
  @endif
</x-container>
</x-page>
