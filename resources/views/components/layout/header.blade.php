@push('header.navbar-class', 'navbar')
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
            <x-icon icon="solid/bars" class="navbar__toggle-icon" />
        </label>

        {{-- The actual toggle (without JS) --}}
        <input type="checkbox" class="navbar__toggle-box" id="navbar-toggle" />

        {{-- Main section --}}
        <ul class="navbar__nav" data-content="navigation" data-toggle-class="navbar__nav--visible">
            @if (($lustrumNav ?? false))
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/">Lustrum</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('home') }}">Gumbo Millennium website</a>
            </li>
            @else
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
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('shop.home') }}">Shop</a>
                <ul class="navbar__dropdown">
                    <li class="navbar__dropdown-item">
                        <a href="{{ route('shop.order.index') }}" class="navbar__dropdown-link">Bestellingen</a>
                    </li>
                </ul>
            </li>
            @endif
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="{{ route('news.index') }}">Nieuws</a>
            </li>
            @endif
        </ul>
    </div>
</nav>
