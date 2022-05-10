<x-page :title="['Bewerken', $album->name, 'Galerij']">
  <x-sections.header
    title="Album bewerken"
    subtitle="Voeg omschrijvingen toe en verberg of verwijder foto's"
    :crumbs="['/' => 'Home', '/gallery' => 'Galerij', '/gallery/{$album->slug}' => $album->name]"
    />

  <x-container space="tiny">
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('gallery.album.edit', $album) }}">
    @csrf
    @method('PATCH')
    <div class="leading-loose mb-4">
      <p>
        Maak hieronder de aanpassingen die je wil doorvoeren. Je ziet alleen de foto's die je mág bewerken.
      </p>
    </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
      @foreach ($photos as $photo)
      <x-gallery.photo-edit :photo="$photo" />
      @endforeach ($photos as $photo)
    </div>

    <div>
      <button type="submit" class="btn btn-primary">Opslaan</button>
    </div>
  </form>
  </x-container>
</x-page>
