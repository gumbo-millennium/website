<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('Title', 'Gumbo Millennium')</title>

    @if (config('gumbo.beta'))
    <meta name="robots" value="noindex,nofollow" />
    @endif

    <link rel="stylesheet" href="{{ mix('/css/gumbo-millennium.css') }}">
    <script src="{{ mix('/js/gumbo-millennium.js') }}" defer></script>
</head>

<body>
    @include('layout.header')

    <main class="main">
        @yield('content')
    </main>

    @include('layout.footer')
</body>

</html>
