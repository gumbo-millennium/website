<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Title, meta tags and JSON-LD --}}
    {!! SEO::generate(config('app.debug') !== true) !!}

    {{-- Stylesheet --}}
    <link rel="stylesheet" href="{{ mix('app.css') }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Poppins:500,700&display=swap" rel="stylesheet">

    {{-- Inlie stylesheets --}}
    @stack('main.styles')

    {{-- Javascript (deferred) --}}
    @section('main.scripts')
    <script src="{{ mix('vendor.js') }}" defer></script>
    <script src="{{ mix('app.js') }}" defer></script>
    @show
</head>

<body>
    {{-- Container --}}
    <div class="container">
        <a href="#content" class="a11y-skip">Ga direct naar inhoud</a>
    </div>

    @section('main.header')
    @include('layout.header')
    @show

    {{-- Main content --}}
    <main class="main" id="content">
        @yield('content')
    </main>

    {{-- Sponsor --}}
    @if ($sponsorService->hasSponsor())
        @php
        $sponsor = $sponsorService->getSponsor();
        @endphp
        <aside>
            @includeWhen($sponsor->is_classic, 'layout.sponsors.classic', compact('sponsor', 'sponsorService'))
            @includeWhen(!$sponsor->is_classic, 'layout.sponsors.modern', compact('sponsor', 'sponsorService'))
        </aside>
    @endif

    {{-- Footer --}}
    @section('main.footer')
    @include('layout.footer')
    @includeWhen($user !== null, 'layout.logout')
    @show
</body>

</html>
