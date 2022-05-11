@props([
  'title',
  'href',
  'image' => null,
  'lead' => null,
  'footer' => null,
  'footerTitle' => null,
  'footerText' => null,
  'footerIcon' => null,
])
<div class="flex flex-col rounded-lg shadow-lg overflow-hidden">
  <div class="flex-shrink-0">
    @if ($image)
    <picture class="h-48 w-full bg-gray-200">
      <source src="{{ image_asset($image)->preset('tile')->webp() }}" type="image/webp" />
      <img src="{{ image_asset($image)->preset('tile') }}" alt="{{ $title }}" class="h-48 w-full object-cover" />
    </picture>
    @else
    <x-empty-state.image class="h-48" />
    @endif
  </div>
  <div class="flex-1 bg-white p-6 flex flex-col justify-between">
    <div class="flex-1">
      @if ($lead)
      <p class="text-sm font-medium text-brand-600">
        {{ $lead }}
      </p>
      @endif
      <a href="{{ $href }}" class="block mt-2">
        <p class="text-xl font-semibold text-gray-900">{{ $title }}</p>
        <p class="mt-3 text-base text-gray-500">
          {{ $slot ?? $description }}
        </p>
      </a>
    </div>
    @if ($footer)
      <div class="mt-6 flex items-center">
        {{ $footer }}
      </div>
    @elseif ($footerTitle)
    <div class="mt-6 flex items-center">
      @if ($footerIcon)
      <div class="flex-shrink-0 mr-3">
        <x-icon :icon="$footerIcon" class="h-10 w-10" />
        @if ($footerIconCaption ?? null)
        <span class="sr-only">{{ $footerIconCaption }}</span>
        @endif
      </div>
      @endif
      <div>
        <p class="text-sm font-medium text-gray-900">
          {{ $footerTitle }}
        </p>
        @if ($footerText)
        <div class="flex space-x-1 text-sm text-gray-500">
          {{ $footerText }}
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>
