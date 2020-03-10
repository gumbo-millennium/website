<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Title, meta tags and JSON-LD --}}
    {!! SEO::generate(config('app.debug') !== true) !!}

    {{-- Stylesheet --}}
    <link rel="stylesheet" href="{{ mix('/app.css') }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Poppins:500,700&display=swap" rel="stylesheet">

    {{-- Inlie stylesheets --}}
    @stack('main.styles')

    {{-- Javascript (deferred) --}}
    @section('main.scripts')
    <script src="{{ mix('/vendor.js') }}" defer></script>
    <script src="{{ mix('/app.js') }}" defer></script>
    @show
</head>

<body>
    @section('main.header')
    @include('layout.header')
    @show

    <main class="main">
        @yield('content')
    </main>

    @section('main.footer')
    @include('layout.footer')
    @includeWhen($user !== null, 'layout.logout')
    @show
</body>

</html>
