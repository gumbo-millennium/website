<div class="rounded-md p-4 {{ $containerColor }}" role="alert" x-data="{ show: true }">
  <div class="flex" x-if="show">
    {{-- Icon --}}
    <div class="flex-shrink-0" role="none">
      <div class="w-5">
        <x-icon :icon="$iconName" class="h-5 {{ $iconColor }}" />
      </div>
    </div>

    {{-- Message --}}
    <div class="ml-3 flex-grow">
      <h3 class="text-sm font-medium {{ $textColor }}">{{ $title }}</h3>

      <div class="mt-2 text-sm {{ $bodyColor }}">
        {{ $slot }}
      </div>

      @if ($errors)
      <div class="mt-2 text-sm {{ $bodyColor }}">
        <ul role="list" class="list-disc pl-5 space-y-1">
        @foreach ($errors as $error)
          <li>{{ $error }}</li>
        @endforeach
        </ul>
      </div>
      @endif
    </div>

    {{-- Dismiss --}}
    @if ($dismissable)
    <div class="ml-auto pl-3">
      <div class="-mx-1.5 -my-1.5">
        <button type="button"
          class="inline-flex {{ $containerColor }} rounded-md p-1.5 {{ $iconColor }} focus:outline-none focus:ring-2"
          @click.prevent="open = false">
          <span class="sr-only">Sluiten</span>
          <x-icon icon="solid/xmark" class="h-5" aria-hidden="true" />
        </button>
      </div>
    </div>
  </div>
  @endif
</div>
