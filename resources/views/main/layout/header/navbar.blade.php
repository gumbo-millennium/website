@php
$user = auth()->user();
@endphp
{{-- Class names --}}
@push('navbar-classes')
navbar navbar-expand-lg navbar-dark
@endpush

{{-- Make navbar transparent if page requires it --}}
@if (isset($page) && $page->meta->navbar_transparent !== 'yes')
@push('navbar-classes')
navbar--opaque
@endpush
@endif

<nav class="@stack('navbar-classes')" role="navigation">
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

        {{-- Navigation, retrieved from WordPress --}}
        <div class="collapse navbar-collapse justify-content-end" id="navbar-collapse">
            @if ($menuHelper->hasLocation('header'))
            <ul class="navbar-nav navbar-nav--flex">
                @forelse ($menuHelper->location('header') as $menuItem)
                {{-- Loop throuh menus --}}

                @if ($menuItem['children'])
                {{-- Menu item with submenu --}}
                <li class="nav-item dropdown">

                    {{-- Render text and arrow down --}}
                    <a href="{{ $menuItem['url'] }}" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        {{ $menuItem['title'] }}
                        {{-- <i class="fal fa-chevron-down"></i> --}}
                    </a>

                    {{-- Render child nodes --}}
                    <div class="dropdown-menu" role="menu">
                        {{-- Loop through children --}}
                        @foreach ($menuItem['children'] as $subMenu)
                        <a class="dropdown-item" href="{{ $subMenu['url'] }}">{{ $subMenu['title'] }}</a>
                        @endforeach
                        {{-- End of submenu --}}
                    </div>
                </li>
                @else
                {{-- Menu item --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ $menuItem['url'] }}">{{ $menuItem['title'] }}</a>
                </li>
                @endif
                @empty

                {{-- Render home link --}}
                <li class="nav-item">
                    <a class="nav-link" href="/">Homepage</a>
                </li>

                @endforelse

                {{-- News and activities --}}
                <li class="nav-item dropdown">
                    {{-- Render text and arrow down --}}
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" data-no-append="true">
                        Nieuws & activiteiten
                    </a>

                    {{-- Menu --}}
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="{{ route('activity.index') }}">Activiteiten</a>
                        <a class="dropdown-item" href="{{ route('news.index') }}">Nieuws</a>
                    </div>
                </li>

            </ul>
            @endif
            <div class="d-lg-none">
                {{-- Render join button if NOT a member --}}
                @unlessrole('member')
                <a class="btn btn-sign-up btn-sign-up--block" href="{{ route('join.form') }}">Word lid</a>
                @endunlessrole

                <ul class="navbar-nav navbar-nav--flex">
                    @auth
                    {{-- Show the user's name --}}
                    <li class="nav-item userbar__nav-item userbar__nav-item--user">
                        <div class="nav-link">
                            Hallo {{ $user->name }}
                        </div>
                    </li>

                    {{-- Render admin link if allowed --}}
                    @can('admin')
                    <li class="nav-item userbar__nav-item">
                        <a class="nav-link userbar__nav-link" href="{{ Nova::path() }}">
                            Admin
                        </a>
                    </li>
                    @endcan

                    {{-- Add logout link --}}
                    <li class="nav-item userbar__nav-item">
                        <a class="nav-link userbar__nav-link" href="{{ route('logout') }}" data-action="submit-form" data-target="navbar-logout-form">
                            Uitloggen
                        </a>
                    </li>
                    @else
                    <li class="nav-item userbar__nav-item">
                        <a class="nav-link userbar__nav-link" href="{{ route('login') }}">
                            Registreren
                        </a>
                    </li>
                    <li class="nav-item userbar__nav-item">
                        <a class="nav-link userbar__nav-link" href="{{ route('login') }}">
                            Inloggen
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</nav>
