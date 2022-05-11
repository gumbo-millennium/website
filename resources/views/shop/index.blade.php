<x-page title="Webshop">
  <x-sections.header
    :crumbs="['/' => 'Home',]"
    title="Gumbo Millennium webshop"
    subtitle="Want de beste merchandise is natuurlijk Gumbo-groen"
  >
  <x-slot name="buttons">
    <x-button :href="route('shop.order.index')" size="small">
      Mijn bestellingen
    </x-button>
  </x-slot>
  </x-sections.header>

  <x-container space="small">
    @if ($advertisedProduct)
    <div class="mb-6">
      <x-shop.advert :product="$advertisedProduct" />
    </div>
    @endif

    @if ($categories->isNotEmpty())
    <x-card-grid>
      @foreach ($categories as $category)
      <x-cards.shop-category :category="$category" />
      @endforeach
    </x-card-grid>
    @else
    <x-empty-state.message title="Geen producten beschikbaar">
      Er zijn helaas momenteel geen producten beschikbaar in de webshop.
    </x-empty-state.message>
    @endif
  </x-container>
</x-page>
