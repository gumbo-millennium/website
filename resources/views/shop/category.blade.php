<x-page :title="[$category->name, 'Webshop']" hide-flash="true">
  <x-sections.header
    :crumbs="[
      '/' => 'Home',
      route('shop.home') => 'Webshop',
    ]"
    :title="$category->name"
    :subtitle="$category->description"
    />

  <x-container space="small">
    @if ($products->isNotEmpty())
    <x-card-grid>
      @foreach ($products as $product)
      <x-cards.shop-product :product="$product" />
      @endforeach
    </x-card-grid>
    @else
    <x-empty-state.message title="Geen producten beschikbaar">
      Er zijn helaas momenteel geen producten beschikbaar in deze categorie.
    </x-empty-state.message>
    @endif
  </x-container>
</x-page>
