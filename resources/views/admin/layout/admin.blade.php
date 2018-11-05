<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    {{-- Standards --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @stack('stack.meta-tags')

    {{-- Page title --}}
    <title>
        @section('title')
        Gumbo Millennium
        @show
    </title>

    @push('stack.css')
    <link href="{{ mix('/gumbo-admin.css') }}" rel="stylesheet">
    @endpush

    {{-- Stylesheets, icons and SEO links --}}
    @include('layout.links')
</head>
<body class="@stack('stack.body-class')">
    {{-- Continue to content link --}}
    <a href="#start-of-content" tabindex="1" class="skip-to-content">Skip to content</a>

    {{-- Before layout --}}
    @yield('layout.content-before')

    {{-- Jump-to-content target --}}
    <div class="sr-only sr-start-of-content" id="start-of-content"></div>

    {{-- Content block --}}
    @yield('layout.content')

    {{-- Layout content --}}
    @yield('layout.content-after')

    {{-- Scripts --}}
    @include('layout.scripts')

    {{-- Additional scripts --}}
    @stack('stack.scripts')
</body>
</html>
