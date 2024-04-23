@props([
  'title' => null,
  'footnote' => null,
  'logo' => false,
  'noDots' => false,
])
<div class="container container-sm my-32">
  @if ($logo)
  <div class="flex flex-col items-center mb-16" role="presentation">
    <img src="{{ Vite::image('images/logo-text-green.svg') ?? 'images/logo-text-green.svg' }}" alt="Gumbo Millennium"
      class="block" width="250" height="100" />
  </div>
  @endif

  @unless ($noDots)
  <div class="flex flex-row items-center justify-center my-8 gap-4">
    <div class="h-4 w-4 rounded-full bg-gray-200 loading-dot"></div>
    <div class="h-4 w-4 rounded-full bg-gray-200 loading-dot"></div>
    <div class="h-4 w-4 rounded-full bg-gray-200 loading-dot"></div>
  </div>
  @endunless

  <h1 class="heading-1 text-center mb-8">{{ $title ?? $slot }}</h1>

  <div class="text-gray-600 text-center text-sm">{{ $footnote }}</div>
</div>
