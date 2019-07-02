<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    {{-- Scripts --}}
    {{-- <script src="{{ mix('js/app.js') }}" defer></script> --}}

    {{-- Styles --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100 body">
    <div id="app" class="app">
        <main class="container mx-auto">
            @yield('content')
        </main>
        <footer class="max-w-sm mx-auto text-center mt-6">
            <a href="https://esetup.nl" rel="friend" target="_blank" class="text-center text-gray-500 text-xs">
                © eSetup B.V. {{ today()->year }}, alle rechten voorbehouden.
            </a>
        </footer>
    </div>
</body>
</html>
