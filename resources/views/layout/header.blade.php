@push('header.userbar-class', 'userbar')
@push('header.navbar-class', 'navbar')
<div class="@stack('header.userbar-class')" role="complementary">
    <div class="container userbar__container">
        {{-- Contact details --}}
        <ul class="userbar__links userbar__links--contact">
            <!--sse-->
            <li class="userbar__links-link">
                <a href="tel:+31388450100" class="userbar__links-item userbar__links-item--flex">
                    @icon('solid/phone', ['aria-label' => 'Telefoon symbool'])
                    <span>038 845 0100</span>
                </a>
            </li>
            <li class="userbar__links-link">
                <a href="mailto:bestuur@gumbo-millennium.nl" class="userbar__links-item userbar__links-item--flex">
                    @icon('solid/envelope', ['aria-label' => 'E-mail symbool'])
                    <span>bestuur@gumbo-millennium.nl</span>
                </a>
            </li>
            <!--/sse-->
        </ul>

        {{-- User info --}}
        <ul class="userbar__links userbar__links--user">
            @auth
            {{-- User name --}}
            <li class="userbar__links-link cursor-default">
                <a href="{{ route('account.index') }}" class="userbar__links-item">
                    @icon('solid/user', ['aria-label' => 'Gebruiker symbool'])
                    <span>{{ $user->name }}</span>
                </a>
            </li>
            @can('enter-admin')
            {{-- Admin link --}}
            <li class="userbar__links-link">
                <a href="{{ url(Nova::path()) }}" class="userbar__links-item">
                    Administratie
                </a>
            </li>
            @endcan
            {{-- Log out button --}}
            <li class="userbar__links-link">
                <button class="userbar__links-item appearance-none" type="submit" form="logout-form">Uitloggen</button>
            </li>
            @else
            {{-- Register link --}}
            <li class="userbar__links-link">
                <a href="{{ route('register') }}" class="userbar__links-item">
                    Registreren
                </a>
            </li>

            {{-- Login link --}}
            <li class="userbar__links-link">
                <a href="{{ route('login') }}" class="userbar__links-item">
                    Inloggen
                </a>
            </li>

            @endauth
        </ul>
    </div>
</div>

{{-- Actual navbar --}}
<nav class="@stack('header.navbar-class')">
    <div class="container navbar__container">
        <a href="{{ route('home') }}" class="logo-wrapper">
            <img src="{{ mix('/images/logo-text-green.svg') }}" alt="Gumbo Millennium" aria-label="Logo Gumbo Millennium"
                class="logo" width="160" height="64" />
        </a>
        <div class="navbar__filler"></div>
        <ul class="navbar__nav">
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('home') }}">Home</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ url('/about') }}">Over</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('activity.index') }}">Activiteiten</a>
            </li>
            @if ($user && $user->is_member)
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('files.index') }}">Bestanden</a>
            </li>
            @endif
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('news.index') }}">Nieuws</a>
            </li>
        </ul>
    </div>
</nav>

{{-- Flashed messages --}}
@if (flash()->message)
<div class="container mt-2" role="alert">
    <div class="notice {{ flash()->class }}">
        <p>{{ flash()->message }}</p>
    </div>
</div>
@endif
