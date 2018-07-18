<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    {{-- Standards --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Page title --}}
    <title>Gumbo Millennium</title>

    {{-- Stylesheets, icons and SEO links --}}
    @include('layout.links')
</head>
<body class="@stack('stack.body-class')">
    {{-- Continue to content link --}}
    <a href="#start-of-content" tabindex="1" class="skip-to-content">Skip to content</a>

    {{-- Before layout --}}
    @yield('layout.content-before')

    {{-- Jump-to-content target --}}
    <div class="sr-only" id="start-of-content"></div>

    {{-- Content block --}}
    @yield('layout.content')

    {{-- Layout content --}}
    @yield('layout.content-after')

    {{-- Scripts --}}
    @include('layout.scripts')
</body>
</html>
