<x-page>
  <x-sections.header
    title="Gumbo's Grote Galerij™"
    subtitle="Van zonnige actiefoto's tot duistere dronkenlapjes, je vind ze hier!"
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
    <div class="border-2 border-gray-200 rounded-lg p-8 text-center col-span-4">
      <p class="text-gray-400 text-4xl">
        Er zijn nog geen albums
      </p>
    </div>
    @endif
  </x-container>
</x-page>
