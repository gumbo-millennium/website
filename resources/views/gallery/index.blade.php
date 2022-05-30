<x-page hide-flash="true">
  <x-sections.header
    title="Gumbo's Grote Galerij™"
    subtitle="Van zonnige actiefoto's tot duistere dronkenlapjes, je vindt ze hier!"
    :crumbs="['/' => 'Home']"
    >
    @can('create', App\Models\Gallery\Album::class)
    <x-slot name="buttons">
      <x-button href="{{ route('gallery.album.create') }}" size="small">
        <x-icon icon="solid/plus" class="h-4 mr-2" />
        Nieuw album
      </x-button>
    </x-slot>
    @endcan
  </x-sections.header>

  <x-container>
    @if ($albums->isNotEmpty())
    <x-card-grid>
      @foreach ($albums as $album)
        <x-cards.album :album="$album" />
      @endforeach
    </x-card-grid>
    @else
    <x-empty-state.message title="Geen albums">
      Er zijn nog geen albums
    </x-empty-state.message>
    @endif
  </x-container>
</x-page>
