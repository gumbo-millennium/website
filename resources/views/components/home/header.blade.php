<div class="bg-gray-900">
  <main class="lg:relative">
    <div class="container w-full pt-16 pb-20 text-center lg:py-36 lg:text-left">
      <div class="px-4 lg:w-1/2 sm:px-8 xl:pr-16">
        <h1 class="text-4xl font-title font-bold text-gray-100 sm:text-5xl md:text-6xl lg:text-5xl xl:text-6xl">
          <span class="block">
            <span class="block sm:inline lg:block 2xl:inline">Dubbel L,</span>
            <span class="block sm:inline lg:block 2xl:inline">Dubbel N,</span>
          </span>
          <span class="block text-brand-500">Dubbel genieten</span>
        </h1>
        <p class="mt-3 max-w-md mx-auto text-lg text-gray-300 sm:text-xl md:mt-5 md:max-w-3xl">
          Welkom bij de gezelligste studentenvereniging van Zwolle.
        </p>
        <div class="mt-10 sm:flex sm:justify-center lg:justify-start">
          <x-button color="primary" href="{{ route('join.form') }}">
            Schrijf je in
          </x-button>
          {{-- <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
            <a href="#"
              class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-brand-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
              Live demo </a>
          </div> --}}
        </div>
      </div>
    </div>
    <div class="relative w-full h-64 sm:h-72 md:h-96 lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 lg:h-full" role="none">
      <picture>
        {{-- <source src="{{ asset('images/header-bg-fhd.webp') }}" type="image/webp"> --}}
        <img class="absolute inset-0 w-full h-full object-cover bg-brand-600"
          src="{{ asset('images/homepage/scaled-header-4.jpg') }}"
          alt="">
      </picture>
      <div class="inset-0 absolute flex items-center justify-center">
        <img src="{{ mix('images/logo-glass-white.svg') }}"
          alt="Logo Gumbo Millennium"
          class="max-h-[50%] max-w-[50%] object-contain w-full h-full lg:hidden" />
      </div>
    </div>
  </main>
</div>
