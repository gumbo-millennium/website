<x-page title="Het laatste nieuws">
  <x-sections.header title="Het laatste nieuws" :crumbs="['/' => 'Home']">
    <x-slot name="subtitle">
      De laatste updates vanuit Gumbo, of gewoon kneiterveel reclame!
    </x-slot>
  </x-sections.header>

  <x-container space="small">
    <x-card-grid>
    @foreach ($items as $item)
      <x-cards.news :item="$item" />
    @endforeach
    </x-card-grid>

    <div class="mt-5 grid">
      {{ $items->links() }}
    </div>
  </x-container>
</x-page>
