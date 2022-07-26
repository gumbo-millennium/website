<x-page title="Welkom bij studentenvereniging Gumbo Millennium - Dubbel L, Dubbel N, Dubbel genieten!">
  <x-slot name="navbar">
    <x-layout.header class="shadow-none" />
  </x-slot>

  <x-home.header />

  <x-home.sponsors :sponsors="$sponsors" />

  @if ($advertisedProduct)
  <x-home.shop :product="$advertisedProduct" />
  @endif

  <x-home.activities :items="$activities" />

  <x-home.call-to-action />

  <x-home.news :items="$newsItems" />

  <x-home.links />
</x-page>
