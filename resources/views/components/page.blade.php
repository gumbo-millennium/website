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

<body>
  {{-- Content skip --}}
  <a href="#content" class="block bg-brand-600 text-white sr-only focus:not-sr-only">
    <div class="container p-4 text-center font-medium">
      <span>Ga direct naar inhoud</span>
    </div>
  </a>

  {{-- Header --}}
  <x-layout.header />

  {{-- Email verification banner --}}
  @if (Auth::user()?->hasVerifiedEmail() === false)
  <x-layout.verify-banner />
  @endif

  {{-- Alerts --}}
  @if (! $hideFlash && flash()->message)
  <div class="container">
    <x-alert :message="flash()->message" :level="flash()->class" />
  </div>
  @endif

  {{-- Content main --}}
  <main class="main" id="content">
    {{ $slot }}
  </main>

  {{-- Sponsor --}}
  <x-layout.sponsor />

  {{-- Footer --}}
  <x-layout.footer />

  {{-- Logout form --}}
  @if (Auth::check())
  <form class="hidden" action="{{ route('logout') }}" method="post" id="logout-form" name="logout-form" role="none" aria-hidden="true">
    @csrf
    <input type="hidden" name="next" value="{{ Request::url() }}">
  </form>
  @endif
</body>
