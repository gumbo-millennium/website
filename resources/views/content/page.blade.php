<?php
// Set the metadata
SEOMeta::setTitle($page->title);
SEOMeta::setCanonical($page->url);

$crumbs = ['/' => 'Home'];
if ($page->group) {
  $crumbs["/$page->group"] = Str::title($page->group);
}
?>
<x-page :title="$page->title" hide-flash="true">
  <article>
    <header>
      <x-sections.header :title="$page->title" :subtitle="$page->tagline" :crumbs="$crumbs" />
    </header>

    {{-- Body --}}
    <x-container space="small" class="leading-loose prose">
      {{ $page->html }}
    </x-container>
  </article>
</x-page>
