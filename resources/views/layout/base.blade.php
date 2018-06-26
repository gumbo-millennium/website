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
    <body>
        {{-- Continue to content link --}}
        <a href="#start-of-content" tabindex="1" class="sr-jump-to-body">Skip to content</a>

        {{-- Non-floating elements --}}
        <div class="layout">

            {{-- Before layout --}}
            @yield('layout.content-before')

            {{-- The main wrapper --}}
            <div class="wrapper">
                {{-- Jump-to-content target --}}
                <div class="sr-only" id="start-of-content"></div>

                {{-- Content block --}}
                @yield('layout.content')
            </div>

            {{-- Layout content --}}
            @yield('layout.content-after')
        </div>

        {{-- Scripts --}}
        @include('layout.scripts')
    </body>
</html>
