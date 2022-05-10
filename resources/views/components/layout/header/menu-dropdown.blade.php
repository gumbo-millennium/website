@props([
  'items' => [],
  'header' => null,
  'footer' => null,
])
<div class="relative" x-data="{ open: false }">
  <!-- Item active: "text-gray-900", Item inactive: "text-gray-500" -->
  <button type="button"
    @click.prevent="open = ! open"
    class="text-gray-500 group bg-white rounded-md inline-flex items-center text-base font-medium hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
    ::aria-expanded="open">
    <span>{{ $slot }}</span>
    <x-icon aria-hidden="true" icon="solid/chevron-down" class="text-gray-400 ml-2 h-5 w-5 group-hover:text-gray-500" />
  </button>
  <div
    x-cloak
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-1"
    @click.outside.prevent="open = false"
    class="absolute -ml-4 mt-3 transform z-20 px-2 w-screen max-w-sm sm:px-0 lg:ml-0 lg:left-1/2 lg:-translate-x-1/2"
    >
    <div class="rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden">
      @if ($header ?? null)
      <div class="px-5 pt-6 bg-white">
        {{ $header }}

        <div class="h-6 border-gray-100 border-b"></div>
      </div>
      @endif
      <div class="relative grid gap-6 bg-white px-5 py-6 sm:gap-8 sm:p-8">
        @foreach ($items as $item)
          <x-layout.header.menu-dropdown-item
            :href="$item['href'] ?? '/'"
            :subtitle="$item['subtitle'] ?? null"
            :icon="$item['icon'] ?? null"
          >{{ $item['title'] }}</x-layout.header.menu-dropdown-item>
        @endforeach
      </div>

      @if ($footer)
        <div class="px-5 py-5 bg-gray-50 space-y-6 sm:flex sm:space-y-0 sm:space-x-10 sm:px-8">
          @if(is_iterable($footer))
            @foreach ($footer as $item)
            <div class="flow-root">
              <a href="{{ $item['href'] }}" class="-m-3 p-3 flex items-center rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
                @if ($item['small'] ?? false)
                <x-icon :icon="$item['icon']" class="flex-shrink-0 h-6 w-6 text-gray-400" />
                <span class="sr-only">{{ $item['title'] }}</span>
                @else
                @if ($item['icon'] ?? null)
                <x-icon :icon="$item['icon']" class="flex-shrink-0 h-6 w-6 text-gray-400 mr-3" />
                @endif
                <span>{{ $item['title'] }}</span>
                @endif
              </a>
            </div>
            @endforeach
          @else
            {{ $footer }}
          @endif
        </div>
      @endif
    </div>
  </div>
</div>
