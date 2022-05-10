<x-container background="gray">
  @if ($back)
  <nav class="sm:hidden" aria-label="Back">
    <a href="{{ $back }}" class="flex items-center font-medium text-gray-500 hover:text-gray-700">
      <!-- Heroicon name: solid/chevron-left -->
      <x-icon icon="solid/chevron-left" class="flex-shrink-0 -ml-1 mr-1 h-5 w-5 text-gray-400" />
      Terug
    </a>
  </nav>
  @endif

  {{-- Desktop breadcrumbs --}}
  @if ($crumbs)
  <nav class="hidden sm:flex" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-4">
      @foreach ($crumbs as $link => $label)
      <li>
        <div class="flex items-center">
          @if ($loop->first)
            <a href="{{ $link }}" class="font-medium text-gray-500 hover:text-gray-700 max-w-sm truncate">{{ $label }}</a>
          @else
            <x-icon icon="solid/chevron-right" class="flex-shrink-0 h-5 w-5 text-gray-400" />
            <a href="{{ $link }}" class="ml-4 font-medium text-gray-500 hover:text-gray-700 max-w-sm truncate">{{ $label }}</a>
          @endif
        </div>
      </li>
      @endforeach
    </ol>
  </nav>
  @endif

  {{-- Title main --}}
  <div class="mt-2 md:flex md:items-center md:justify-between">
    {{-- Page title --}}
    <div class="flex-1 min-w-0">
      <h2 class="text-4xl font-bold font-title leading-relaxed text-gray-900 sm:text-5xl pb-2 lg:truncate">{{ $title }}</h2>

      @if ($subtitle)
      <p class="text-lg font-medium text-gray-500">{{ $subtitle }}</p>
      @elseif ($stats)
      <div class="flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
        @foreach ($stats as $stat)
        <div class="mt-2 flex items-center text-sm text-gray-500">
          @if ($stat['icon'] ?? null)
          <div class="w-4 mr-2">
            <x-icon :icon="$stat['icon']" class="flex-shrink-0 mr-1.5 h-4 text-gray-400" />
          </div>
          @endif
          <span>{{ $stat['label'] }}</span>
        </div>
        @endforeach
      </div>
      @endif
    </div>

    {{-- Page buttons --}}
    @if ($buttons)
    <div class="mt-4 flex-shrink-0 flex md:mt-0 md:ml-4 gap-x-4">
      {{ $buttons }}
    </div>
    @endif
  </div>
</x-container>
