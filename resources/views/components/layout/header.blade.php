<div class="relative bg-white shadow {{ $transparent ? 'lg:shadow-none lg:bg-none' : ''}}" x-data="{ menuOpen: false }">
  <div class="mx-auto px-4 sm:px-6 lg:container">
    <div class="flex justify-between items-center py-6 lg:justify-start lg:space-x-10">
      <div class="flex justify-start lg:w-0 lg:flex-1">
        <a href="{{ route('home') }}">
          <span class="sr-only">Gumbo Millennium</span>
          <img class="h-10 w-auto sm:h-10" src="{{ Vite::image('images/logo-text-green.svg') }}" alt="Logo">
        </a>
      </div>
      @unless ($simple)
      <div class="-mr-2 -my-2 lg:hidden">
        <button type="button"
          @click.prevent="menuOpen = ! menuOpen"
          class="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-500"
          aria-expanded="false">
          <span class="sr-only">Open menu</span>
          <x-icon class="h-6" icon="solid/bars" />
        </button>
      </div>
      <nav class="hidden lg:flex space-x-10">
        @foreach ($desktopMenuItems as $menuItem)
          @if ($menuItem['href'] ?? null)
          <x-layout.header.menu-item :href="url($menuItem['href'])">
            {{ $menuItem['title'] }}
          </x-layout.header.menu-item>
          @else
          <x-layout.header.menu-dropdown :items="$menuItem['items']" :footer="$menuItem['footer'] ?? null">
            {{ $menuItem['title'] }}
          </x-layout.header.menu-dropdown>
          @endif
        @endforeach
      </nav>
      <div class="hidden lg:flex items-center justify-end flex-1 w-0">
        @if ($user)
        {{-- Dropdown with account actions --}}
        <x-layout.header.menu-dropdown :items="$accountLinks">
          {{ $user->first_name }}

          <x-slot name="footer">
            <button type="submit" form="logout-form"
              class="-m-3 p-3 flex items-center rounded-md text-base font-medium text-gray-900 hover:bg-gray-100">
              <x-icon icon="solid/right-from-bracket" class="flex-shrink-0 h-6 w-6 text-gray-400 mr-3" />
              <span>Uitloggen</span>
            </a>
          </x-slot>
        </x-layout.header.menu-dropdown>

        {{-- Join us button --}}
        @if (! $user->is_member)
          <a href="{{ route('join.form')}}"
            class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-600 hover:bg-brand-700">
            Word lid
          </a>
        @endif
        @else
        <a href="{{ route('login') }}" class="whitespace-nowrap text-base font-medium text-gray-500 hover:text-gray-900">
          Log in
        </a>
        <a href="{{ route('join.form')}}"
          class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-600 hover:bg-brand-700">
          Word lid
        </a>
        @endif
        @if (Cart::getContent()->count() > 0)
        <a href="{{ route('shop.cart') }}"
          class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-600 hover:bg-brand-700">
          <div class="mr-3 w-6">
            <x-icon icon="solid/cart-shopping" class="h-6" />
          </div>

          {{ Str::price(Cart::getTotal()) }}
        </a>
        @endif
      </div>
      @endunless
    </div>
  </div>

  @unless ($simple)
  <div
    class="absolute top-0 inset-x-0 z-20 w-full p-2 transition transform origin-top-right md:left-[unset] md:max-w-xl lg:hidden"
    x-show="menuOpen"
    x-cloak
    @click.outside="menuOpen = false"
    x-transition:enter="duration-200 ease-out"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="duration-100 ease-in"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    >
    <div class="rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 bg-white divide-y-2 divide-gray-50">
      <div class="pt-5 pb-6 px-5">
        <div class="flex items-center justify-between">
          <div class="md:invisible">
            <img class="h-8 w-auto" src="{{ Vite::image('images/logo-text-green.svg') }}" alt="Gumbo Millennium">
          </div>
          <div class="-mr-2">
            <button type="button"
              @click.prevent="menuOpen = false"
              class="bg-white rounded-md p-2 inline-flex items-center justify-center text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-500">
              <span class="sr-only">Sluit menu</span>
              <div class="w-6" aria-hidden="true">
                <x-icon icon="solid/xmark" class="h-6" />
              </div>
            </button>
          </div>
        </div>
        <div class="mt-6">
          <nav class="grid gap-y-8">
            @foreach ($mobileMenuItems as $menuItem)
            <a href="{{ url($menuItem['href']) }}" class="-m-3 p-3 flex items-center rounded-md hover:bg-gray-50">
              <div class="w-6 flex-shrink-0 text-center">
                <x-icon :icon="$menuItem['icon']" class="h-6 text-brand-600" />
              </div>
              <span class="ml-3 text-base font-medium text-gray-900">{{ $menuItem['title'] }}</span>
            </a>
            @endforeach
          </nav>
        </div>
      </div>
      <div class="py-6 px-5 space-y-6">
        <div class="grid grid-cols-2 gap-y-4 gap-x-8">
          @foreach ($mobileMenuFooter as $menuItem)
          <a href="{{ url($menuItem['href']) }}" class="text-base font-medium text-gray-900 hover:text-gray-700">
            {{ $menuItem['title'] }}
          </a>
          @endforeach
        </div>
        @if ($user)
        <div class="pt-4 border-t border-gray-200">
          <div class="flex items-center">
            <div class="rounded-full h-10 w-10 bg-brand-500 flex justify-center items-center flex-shrink-0">
              <x-icon icon="solid/user" class="h-6 text-white" />
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-gray-800">{{ $user->name }}</div>
              <div class="text-sm font-medium text-gray-500">{{ $user->email }}</div>
            </div>
            <button type="submit" form="logout-form"
              class="ml-auto bg-white flex-shrink-0 p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
              <span class="sr-only">Log uit</span>
              <x-icon icon="solid/right-from-bracket" class="h-6 w-6" />
            </button>
          </div>
          <nav class="mt-6 grid gap-y-4">
            @foreach ($accountLinks as $link)
            <a href="{{ $link['href'] }}" class="text-base font-medium text-gray-900 hover:text-gray-700">
              {{ $link['title'] }}
            </a>
            @endforeach
          </nav>

          @if (! $user->is_member)
              <a href="{{ route('join.form') }}"
                class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-600 hover:bg-brand-700">
                Word lid
              </a>
          @endif
        </div>
        @else
        <div>
          <a href="{{ route('join.form') }}"
            class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-600 hover:bg-brand-700">
            Word lid
          </a>
          <p class="mt-6 text-center text-base font-medium text-gray-500">
            Al lid?
            <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-500"> Log in </a>
          </p>
        </div>
        @endif
      </div>
    </div>
  </div>
  @endunless
</div>
