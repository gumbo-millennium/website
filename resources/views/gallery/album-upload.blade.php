<x-page :title="['Foto\'s uploaden', $album->name, 'Galerij']" hide-flash="true">
  <x-sections.header
    title="Foto's uploaden"
    :subtitle='"Voeg je beste kiekjes toe aan {$album->name}"'
    :crumbs="['/' => 'Home', '/gallery' => 'Galerij', '/gallery/{$album->slug}' => $album->name]"
    />

  <x-container space="tiny">
    <form method="POST" action="{{ route('gallery.album.upload', $album) }}">
      @csrf

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <x-gallery.step number="1" title="Akkoord met de voorwaarden">
        <div class="leading-loose mb-4">
          <p>
            We verwachten dat je zelfcensuur toepast waar nodig. De foto's zijn alleen te zien voor leden,
            maar dat betekent niet dat je dronkemansfoto's van LHW om 06:30 verstanding zijn om te publiceren.
          </p>
          <p>
            Bij misbruik worden de foto's verwijderd en kan je geblokkeerd worden van het uploaden.
          </p>
        </div>
        <div  class="flex flex-row form__field form__field--checkbox">
          <input type="checkbox" name="accept-terms" id="accept-terms" required class="form__field-input form__field-input--checkbox form-checkbox">
          <label class="form__field-label" for="accept-terms">Ik ga akkoord met de voorwaarden</label>
        </label>
      </x-gallery.step>

    <x-gallery.step number="2" title="Bestanden uploaden">
      <div class="leading-loose mb-4">
        <p>
          Vergeet niet na het uploaden op opslaan te klikken, anders verschijnen ze nooit online 😔
        </p>
      </div>

      <div data-content="filepond" data-scope="gallery"
        data-max-filesize="{{ config('gumbo.gallery.max_photo_size') }}"
        data-process-url="{{ route('gallery.filepond.process', $album) }}"
        data-revert-url="{{ route('gallery.filepond.revert', $album) }}"
        data-csrf="{{ csrf_token() }}">
        <script type="application/json" data-content="pending-uploads">@json($pendingPhotos, JSON_PRETTY_PRINT)</script>
        <input name="file" multiple required accept="image/jpeg" type="file" class="hidden" />
      </div>

      <p class="text-sm text-gray-primary-1">
        Uploads die niet zijn opgeslagen blijven sowieso 3 uur staan, daarna kunnen ze worden verwijderd.
      </p>
    </x-gallery.step>

    <x-gallery.step number="3" title="Opslaan">
      <div class="leading-loose mb-4">
        <p>
          Klik hieronder op opslaan om de foto's hierboven te publiceren.
        </p>
      </div>

      <div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
      </div>
    </x-gallery.step>
    </form>
  </x-container>
</x-page>
