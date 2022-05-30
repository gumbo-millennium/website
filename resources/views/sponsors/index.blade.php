<x-page title="Onze Sponsoren" hide-flash="true">
  <x-sections.header
    title="Sponsoren van Gumbo Millennium"
    subtitle="Dankzij deze bedrijven kunnen wij de gaafste activiteiten neerzetten. Bedankt!"
    :crumbs="['/' => 'Home']" />

  {{-- Main sponsors --}}
  <div class="p-8 bg-gray-secondary-2">
      <div class="container">
          <div class="partner-block partner-block--primary">
              @foreach ($branded as $sponsor)
              @include('sponsors.partials.primary')
              @endforeach
          </div>
      </div>
  </div>

  {{-- Second sponsors and list --}}
  <div class="container">
      {{-- Smaller sponsors --}}
      <div class="partner-block partner-block--secondary">
          @foreach ($simple as $sponsor)
          @include('sponsors.partials.secondary')
          @endforeach
      </div>
  </div>
</x-page>
