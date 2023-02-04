<?php
$stats = array_filter([
  [
    'label' => $item->published_at->isoFormat('D MMMM, Y'),
    'icon' => 'regular/calendar',
  ],
  [
    'label' => $item->author?->public_name ?: 'Onbekend',
    'icon' => 'regular/user',
  ],
  $item->sponsor ? [
    'label' => "Gesponsord door {$item->sponsor}",
    'icon' => 'regular/handshake',
  ] : null,
]);

// Share links
$itemUrl = route('news.show', ['item' => $item]);
$facebookQuery = http_build_query(['u' => $itemUrl]);
$genericQuery = http_build_query(['text' => $item->title, 'url' => $itemUrl]);
$whatsappQuery = http_build_query(['text' => "Lees {$item->title}: {$itemUrl}"]);

// Build links
$facebookLink = "https://www.facebook.com/sharer/sharer.php?{$facebookQuery}";
$telegramLink = "https://telegram.me/share/url?{$genericQuery}";
$twitterLink = "http://twitter.com/share?{$genericQuery}";
$whatsappLink = "whatsapp://send?{$facebookQuery}";
?>

<x-page :title="[$item->title, 'Nieuws']" hide-flash="true">
  <article>
    <header>
      <x-sections.header
        :title="$item->title"
        :crumbs="['/' => 'Home', '/nieuws' => 'Nieuws']"
        :stats="$stats">

        {{-- Register share actions --}}
        <x-slot name="buttons">
          <x-button style="primary" href="{{ route('news.index') }}" data-action="share" size="small" class="flex items-center md:hidden">
            <x-icon icon="solid/share-nodes" class="h-5 mr-2" role="none" />
            Delen
          </x-button>

          <x-button color="outline" href="{{ $twitterLink }}" target="_blank" size="small" class="hidden items-center md:flex">
            <x-icon icon="brands/twitter" class="h-5 mr-2" role="none" />
            Tweet
          </x-button>

          <x-button color="outline" href="{{ $facebookLink }}" target="_blank" size="small" class="hidden items-center md:flex">
            <x-icon icon="brands/facebook-f" class="h-5 mr-2" role="none" />
            Facebook
          </x-button>
        </x-slot>
      </x-sections.header>
    </header>

    {{-- Body --}}
    <x-container space="small" class="leading-loose">
      {{-- Headline first --}}
      @if ($item->headline)
      <div class="mb-6">
          <p class="font-bold text-lg">
              {{ $item->headline }}
          </p>
      </div>
      @endif

      {{-- Rest of the HTML --}}
      <div class="prose prose--narrow">
          {{ $item->html }}
      </div>
    </x-container>
  </article>
</x-page>
