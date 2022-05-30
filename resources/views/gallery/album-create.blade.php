<x-page :title="['Nieuw album', 'Galerij']" hide-flash="true">
  <x-sections.header
    title="Album aanmaken"
    subtitle="Je eigen plekje voor jouw beste kiekjes!"
    :crumbs="['/' => 'Home', '/gallery' => 'Galerij']"
    />

  <x-container space="tiny">
    <form method="POST" action="{{ route('gallery.album.create') }}">
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

    <x-gallery.step number="2" title="Naam kiezen">
      <div class="leading-loose mb-4">
        <p>
          Kies de naam van je album. Hij hoeft niet uniek te zijn, maar zorg er voor dat het omschrijft waar het over gaat.
        </p>
      </div>

      <div>
        <input type="text" name="name" id="name" required class="form-input" placeholder="Naam" value="{{ old('name') }}">
        @if ($errors->has('name'))
          <div class="form__field-error">
            {{ $errors->first('name') }}
          </div>
        @endif
      </div>
    </x-gallery.step>

    <x-gallery.step number="3" title="Omschrijven">
      <div class="leading-loose mb-4">
        <p>
          Waar gaat je album over? Schrijf hier een korte omschrijving.
        </p>
      </div>

      <div>
        <textarea name="description" id="description" required class="form-input" placeholder="Omschrijving">{{ old('description') }}</textarea>
        @if ($errors->has('description'))
          <div class="form__field-error">
            {{ $errors->first('description') }}
          </div>
        @endif
      </div>
    </x-gallery.step>

    <x-gallery.step number="4" title="Opslaan">
      <div class="leading-loose mb-4">
        <p>
          Klik hieronder op opslaan om je nieuwe album aan te maken.
        </p>
      </div>

      <div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
      </div>
    </x-gallery.step>
    </form>
  </x-container>
</x-page>
