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
            {{-- Header --}}
            @include('layout.header')

            <div class="wrapper">
                <div class="sr-only" id="start-of-content"></div>
                {{-- Content block --}}
                @yield('content')
            </div>

            {{-- Footer, including image --}}
            @include('layout.footer')

            {{-- Back to top button --}}
            <a class="scroll-top" href="#top"><i class="fa fa-angle-up"></i></a>
        </div>

        {{-- Offcanvas menu --}}
        @include('layout.offcanvas')

        {{-- Scripts --}}
        @include('layout.scripts')
    </body>
</html>
