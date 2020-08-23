@push('header.userbar-class', 'userbar')
@push('header.navbar-class', 'navbar')
<div class="@stack('header.userbar-class')" role="complementary">
    <div class="container userbar__container">
        {{-- Contact details --}}
        <ul class="userbar__links userbar__links--contact">
            <!--sse-->
            <li class="userbar__links-link">
                <a href="tel:+31388450100" class="userbar__links-item userbar__links-item--flex">
                    @svg('solid/phone', ['aria-label' => 'Telefoon symbool'])
                    <span>038 845 0100</span>
                </a>
            </li>
            <li class="userbar__links-link">
                <a href="mailto:bestuur@gumbo-millennium.nl" class="userbar__links-item userbar__links-item--flex">
                    @svg('solid/envelope', ['aria-label' => 'E-mail symbool'])
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
                    @svg('solid/user', ['aria-label' => 'Gebruiker symbool'])
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
            <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" aria-label="Logo Gumbo Millennium"
                class="logo block dark:hidden" width="160" height="64" />
            <img src="{{ mix('images/logo-text-night.svg') }}" alt="Gumbo Millennium" aria-label="Logo Gumbo Millennium"
                class="logo hidden dark:block" width="160" height="64" />
        </a>

        {{-- Push everything to the right --}}
        <div class="navbar__filler"></div>

        {{-- Toggle --}}
        <label for="navbar-toggle" class="navbar__toggle">
            <span class="sr-only">Toon navigatie</span>
            @svg('bars', 'navbar__toggle-icon')
        </label>

        {{-- The actual toggle (without JS) --}}
        <input type="checkbox" class="navbar__toggle-box" id="navbar-toggle" />

        {{-- Main section --}}
        <ul class="navbar__nav" data-content="navigation" data-toggle-class="navbar__nav--visible">
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('home') }}">Home</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ url('/over') }}">Over</a>
                <ul class="navbar__dropdown">
                    <li class="navbar__dropdown-item">
                        <a href="{{ url('/bestuur') }}" class="navbar__dropdown-link">Bestuur</a>
                    </li>
                    <li class="navbar__dropdown-item">
                        <a href="{{ route('group.index', ['group' => 'commissies']) }}" class="navbar__dropdown-link">Commissies</a>
                    </li>
                    <li class="navbar__dropdown-item">
                        <a href="{{ route('group.index', ['group' => 'projectgroepen']) }}" class="navbar__dropdown-link">Projectgroepen</a>
                    </li>
                    <li class="navbar__dropdown-item">
                        <a href="{{ route('group.index', ['group' => 'disputen']) }}" class="navbar__dropdown-link">Disputen</a>
                    </li>
                    <li class="navbar__dropdown-item">
                        <a href="{{ route('sponsors.index') }}" class="navbar__dropdown-link">Sponsoren</a>
                    </li>
                </ul>
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
                <ul class="navbar__dropdown">
                    <li class="navbar__dropdown-item">
                        <a href="{{ route('group.index', ['group' => 'coronavirus']) }}" class="navbar__dropdown-link">Coronavirus</a>
                    </li>
                </ul>
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
