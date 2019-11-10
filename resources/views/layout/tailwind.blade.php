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
    <a href="#start-of-content" tabindex="1" class="sr-only sr-only-focusable jump-to-content">Jump to content</a>

    @include('layout.searchbar')
    @include('layout.userbar')
    @include('layout.navbar')

    <div id="app" class="app">
        <main class="container mx-auto" id="start-of-content">
            @if (flash()->message)
                <div class="{{ flash()->class }}">
                    {{ flash()->message }}
                </div>
            @endif
            @yield('content')
        </main>
        <footer class="max-w-sm mx-auto text-center mt-6">
            <a href="{{ route('home') }}" rel="home" class="text-center text-gray-500 text-xs">
                © Gumbo Millennium {{ today()->year }}, alle rechten voorbehouden.
            </a>
        </footer>
    </div>
</body>
</html>
