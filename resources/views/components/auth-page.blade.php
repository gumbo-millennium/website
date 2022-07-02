<!DOCTYPE html>
<html lang="nl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  {{-- Title, meta tags and JSON-LD --}}
  {!! SEO::generate(! App::hasDebugModeEnabled()) !!}
  {{-- Stylesheet --}}
  <link rel="stylesheet" href="{{ mix('app.css') }}">

  {{-- Google Fonts --}}
  <link href="https://fonts.googleapis.com/css?family=Poppins:500,700&display=swap" rel="stylesheet">

  {{-- Scripts --}}
  <script src="{{ mix('manifest.js') }}" defer></script>
  <script src="{{ mix('vendor.js') }}" defer></script>
  <script src="{{ mix('app.js') }}" defer></script>
</head>

<body class="min-h-screen">
  {{-- Content skip --}}
  <a href="#content" class="block bg-brand-600 text-white sr-only focus:not-sr-only">
    <div class="container p-4 text-center font-medium">
      <span>Ga direct naar inhoud</span>
    </div>
  </a>

  <div class="lg:relative lg:min-h-screen">
    {{-- Header --}}
    <x-layout.header transparent="true" simple="true" />

    {{-- Content --}}
    <main class="container lg:py-8 lg:text-left">
      <div class="px-4 lg:w-1/2 xl:pr-16 flex flex-col items-start">
        {{-- Alerts --}}
        @if (! $hideFlash && flash()->message)
        <div class="container">
          <x-alert :message="flash()->message" :level="flash()->class" />
        </div>
        @endif

        {{-- Content main --}}
        <div id="content" class="flex-grow">
          {{ $slot }}
        </div>

              {{-- Footer --}}
              <div class="flex-none w-full">
                <x-layout.footer simple="true" />
              </div>
      </div>
    </main>

    {{-- Photo room, for now just a gradient --}}
    <div class="hidden w-1/2 h-full absolute inset-y-0 right-0 lg:block">
      <div class="absolute inset-0 w-full h-full object-cover bg-gradient-to-br from-brand-500 to-brand-700">
    </div>
  </div>


  {{-- Logout form --}}
  @if (Auth::check())
  <form class="hidden" action="{{ route('logout') }}" method="post" id="logout-form" name="logout-form" role="none" aria-hidden="true">
    @csrf
    <input type="hidden" name="next" value="{{ Request::url() }}">
  </form>
  @endif
</body>
