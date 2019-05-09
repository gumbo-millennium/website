@inject('menuHelper', 'App\Services\MenuProvider')

{{-- Class names --}}
@push('navbar-classes')
navbar navbar-expand-lg navbar-dark fixed-top
@endpush

{{-- Make navbar transparent if page requires it --}}
@if (isset($page) && $page->meta->navbar_transparent !== 'yes')
@push('navbar-classes')
navbar--opaque
@endpush
@endif

@auth
{{-- Add logout form to header --}}
<form class="d-none" id="navbar-logout-form" action="{{ route('logout') }}" method="post">
    @csrf
</form>
@endauth

{{-- Header v2 --}}
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
        @if ($menuHelper->hasLocation('header'))
        <div class="collapse navbar-collapse justify-content-end" id="navbar-collapse">
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

                @auth
                @php
                $user = auth()->user();
                @endphp

                {{-- Render document system, if allowed --}}
                @can('file-view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('files.index') }}">Documenten</a>
                </li>
                @endcan

                {{-- Render join button if NOT a member --}}
                @unlessrole('member')
                <li class="nav-item">
                    <a class="nav-link nav-link--rounded" href="{{ route('join.form') }}">
                        <span>Word lid</span>
                        <span class="far fa-thumbs-up"></span>
                    </a>
                </li>
                @endunlessrole

                <li class="nav-item nav-separator">&nbsp;</li>

                {{-- Render logout button --}}
                <li class="nav-item dropdown">
                    {{-- Render text and arrow down --}}
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" data-no-append="true">
                        <i class="fas fa-user"></i>
                        {{ $user->name }}
                    </a>

                    {{-- Menu --}}
                    <div class="dropdown-menu" role="menu">
                        @if ($user->hasPermissionTo('admin'))
                        {{-- Add admin link --}}
                        <a class="dropdown-item" href="{{ route('admin.home') }}">Administratiepaneel</a>

                        {{-- Add separator --}}
                        <div class="dropdown-divider"></div>
                        @endif
                        <a class="dropdown-item" href="{{ route('logout') }}" data-action="submit-form" data-target="navbar-logout-form">Uitloggen</a>
                    </div>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link nav-link--rounded" href="{{ route('join.form') }}">
                        <span>Word lid</span>
                        <span class="far fa-thumbs-up"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">
                        <i class="fas fa-lock fa-fw"></i>
                        <span class="d-lg-none">Inloggen</span>
                        <span class="sr-only">Inloggen</span>
                    </a>
                </li>
                @endauth
            </ul>
        </div>
        @endif
    </div>
</nav>
