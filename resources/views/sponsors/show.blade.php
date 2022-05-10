<x-page :title="$sponsor_contents_title ?? $sponsor->name . ' - Sponsoren'">
  <x-sections.header
    :title="$sponsor->contents_title ?? $sponsor->name"
    :stats='["Gesponsord door {$sponsor->name}" => "solid/ad"]'
    :crumbs="['/' => 'Home', '/sponsoren' => 'Sponsoren']">

    <x-slot name="buttons">
      <x-button
        :href="route('sponsors.link', $sponsor)"
        target="_blank"
        size="small"
        color="primary"
        rel="noopener">
        Lees meer
      </x-button>
    </x-slot>
  </x-sections.header>

  <x-container space="small" class="leading-loose prose">
    {!! $sponsor->content_html !!}
  </x-container>
</x-page>
