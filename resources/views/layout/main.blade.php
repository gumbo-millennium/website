<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('Title', 'Gumbo Millennium')</title>

    {{-- Tell robots to fuck off when we're testing --}}
    @if (config('gumbo.beta'))
    <meta name="robots" value="noindex,nofollow" />
    @endif

    {{-- Stylesheet --}}
    <link rel="stylesheet" href="{{ mix('/app.css') }}">

    {{-- Javascript (deferred) --}}
    <script src="{{ mix('/vendor.js') }}" defer></script>
    <script src="{{ mix('/app.js') }}" defer></script>
</head>

<body>
    @include('layout.header')

    <main class="main">
        @yield('content')
    </main>

    @include('layout.footer')
</body>

</html>
