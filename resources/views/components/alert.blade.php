<div {{ $attributes->merge([
  'class' => "rounded-md p-4 $containerColor",
  'role' => "alert",
  'x-data' => "{ show: true }",
  'x-show' => 'show'
]) }}>
  <div class="flex">
    {{-- Icon --}}
    <div class="flex-shrink-0" role="none">
      <div class="w-5">
        <x-icon :icon='$iconName' class="h-5 {{ $iconColor }}" />
      </div>
    </div>

    {{-- Message --}}
    <div class="ml-3 flex-grow">
      <p class="text-sm font-medium {{ $textColor }}">{{ $message ?? $slot }}</p>
    </div>

    {{-- Dismiss --}}
    @if ($dismissable)
    <div class="ml-auto pl-3">
      <div class="-mx-1.5 -my-1.5">
        <button type="button"
          class="inline-flex {{ $containerColor }} rounded-md p-1.5 {{ $iconColor }} focus:outline-none focus:ring-2" @click.prevent="show = false">
          <span class="sr-only">Sluiten</span>
          <x-icon icon="solid/times" class="h-5" aria-hidden="true" />
        </button>
      </div>
    </div>
  </div>
  @endif
</div>
