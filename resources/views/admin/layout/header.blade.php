{{-- Navigation links --}}
@php
$navLinks = [
    ['admin.home', 'Homepage'],
    // ['admin.members', 'Leden'],
    // ['admin.events', 'Evenementen'],
    ['admin.files.index', 'Documenten']
];
@endphp

<div class="container">
    {{-- Super simple header --}}
    <nav class="navbar navbar-expand-lg navbar-light navbar-admin rounded">
        {{-- Home link --}}
        <a class="navbar-brand" href="{{ route('admin.home') }}">
            <img src="/svg/logo-text-green.svg" class="navbar-brand-logo align-top" alt="Gumbo Millennium">
        </a>

        {{-- Navbar toggle --}}
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#admin-navigation" aria-controls="admin-navigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="admin-navigation">
            <ul class="navbar-nav mr-auto">
                @foreach ($navLinks as list($route, $title))
                @php
                $current = request()->routeIs($route);
                @endphp
                <li class="{{ trim('nav-link ' . $current ? 'active' : '') }}">
                    <a class="nav-link" href="{{ route($route) }}">
                        {{ $title }}
                        @if ($current)
                        <span class="sr-only">(huidig)</span>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
            <div class="navbar-text">
                <strong>{{ auth()->user()->display_name }}</strong>
                <button type="submit" form="logout-form" class="btn btn-link btn-sm">
                    <span>Uitloggen</span>
                </button>
            </div>
        </div>
    </nav>
</div>

{{-- Logout form --}}
<form class="d-none" action="{{ route('auth.logout') }}" method="POST" id="logout-form">
    @csrf
</form>
