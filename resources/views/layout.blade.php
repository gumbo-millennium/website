<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('Title', 'Gumbo Millennium')</title>

    @if (config('gumbo.beta'))
    <meta name="robots" value="noindex,nofollow" />
    @endif

    <link rel="stylesheet" href="{{ mix('/css/beta.css') }}">
</head>

<body>
    <nav class="header">
        <a href="{{ route('home') }}" class="logo-wrapper">
            <img src="{{ asset('/images/logo-text-green.svg') }}" alt="Gumbo Millennium" aria-label="Logo Gumbo Millennium"
                class="logo" width="160" height="64" />
        </a>
        <ul class="navbar">
            <li><a href="/">Home</a></li>
            <li><a href="/activities">Activities</a></li>
            <li><a href="/files">Files</a></li>
            <li><a href="/news">News</a></li>
        </ul>
    </nav>
    <main class="main">
        @yield('content')
    </main>
    <footer class="footer">
        © Gumbo Millennium {{ today()->year }}. Alle rechten voorbehouden.
    </footer>
</body>

</html>
