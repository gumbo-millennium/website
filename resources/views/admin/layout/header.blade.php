{{-- Navigation links --}}
@php
$navLinks = [
    ['admin.home', 'Homepage'],
    ['admin.members', 'Leden'],
    ['admin.events', 'Evenementen'],
    ['admin.files', 'Bestanden']
];
@endphp


{{-- Top navigation --}}
<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar--opaque" role="navigation">
    <div class="container no-override">
        {{-- Brand logo --}}
        <a class="navbar-brand" href="/">
            <img src="/svg/logo-text-white.svg" class="navbar-brand__logo" alt="" aria-labelledby="navbar-brand-text" />
            <span class="sr-only" id="navbar-brand-text">Gumbo Millennium</span>
        </a>

        {{-- Expand button for mobile --}}
        <button class="navbar-toggler" data-toggle="collapse" data-target="#navbar-collapse" aria-label="Toon navigatie">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbar-collapse">
            <ul class="navbar-nav">
                @foreach ($navLinks as list($route, $title))
                    @php
                    $routeUrl = route($route);
                    $isCurrent = request()->is(route($route, null, false));
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link{{ $isCurrent ? ' active' : ''}}" href="{{ $routeUrl }}">
                            {{ $title }}
                            @if ($isCurrent)
                            <span class="sr-only">(huidig)</span>
                            @endif
                        </a>
                    </li>
                @endforeach

                <li class="nav-item">
                    <button style="cursor: pointer" type="submit" form="logout-form" class="nav-link nav-link--rounded">
                        <span>Log uit</span>
                        <span class="fas fa-lock"></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Logout form --}}
<form class="hidden" action="{{ route('auth.logout') }}" method="POST" id="logout-form">
    @csrf
</form>

<div class="admin-wrapper">
