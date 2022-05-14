<?php
// Set the metadata
SEOMeta::setTitle($page->title);
SEOMeta::setCanonical(route('group.index', ['group' => $page->slug]));
?>
<x-page title="Het laatste nieuws">
  <x-sections.header :title="$page->title" :subtitle="$page->tagline" :crumbs="['/' => 'Home']" />

  <x-container space="small" class="leading-loose prose">
    {!! $page->html !!}
  </x-container>

  <x-container space="small">
    <x-card-grid>
    @foreach ($pages as $item)
      <x-cards.page :page="$item" />
    @endforeach
    </x-card-grid>
  </x-container>
</x-page>
