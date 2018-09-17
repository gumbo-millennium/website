@inject('menuHelper', 'App\Services\MenuProvider')

{{-- Header v2 --}}
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" role="navigation">
    <div class="container no-override">
        {{-- Brand logo --}}
        <a class="navbar-brand" href="index.html">
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
            <ul class="navbar-nav">
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

                <li class="nav-item">
                    <a class="nav-link nav-link--rounded" href="/sign-up">
                        <span>Word lid</span>
                        <span class="far fa-thumbs-up"></span>
                    </a>
                </li>
            </ul>
        </div>
        @endif
    </div>
</nav>
