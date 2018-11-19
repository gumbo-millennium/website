{{-- Super simple header --}}
<nav class="navbar navbar-light admin-nav rounded">
    {{-- Admin container --}}
    <div class="container admin-container flex-nowrap">
        {{-- Home link --}}
        <a class="admin-nav-brand" href="{{ route('admin.home') }}">
            <img src="/svg/logo-text-green.svg" class="admin-nav-brand-logo align-top" alt="Gumbo Millennium">
        </a>

        <div class="navbar-text ml-auto d-flex flex-nowrap align-items-center">
                {{-- User and logout --}}
            <div class="d-none d-sm-flex flex-nowrap align-items-center">
                <i class="fas fa-user fa-fw d-xs-none" aria-label="Ingelogd als"></i>
                <strong class="mx-2">{{ auth()->user()->name }}</strong>
                <button type="submit" form="logout-form" class="btn btn-outline-brand btn-sm">
                    <i class="fas fa-lock mr-1" aria-label="Padlock"></i>
                    <span class="d-none d-md-inline-block">Uitloggen</span>
                    <span class="sr-only">Uitloggen</span>
                </button>
            </div>

            {{-- Homepage link --}}
            <a href="{{ route('home') }}" class="btn btn-outline-brand btn-sm ml-sm-2">
                <i class="fas fa-fw fa-home"></i>
                <span class="d-none d-md-inline">Homepage</span>
                <span class="sr-only">Homepage</span>
            </a>
        </div>

        {{-- Navbar toggle --}}
        <button class="navbar-toggler d-inline-block d-lg-none ml-3" type="button"
            data-toggle="admin-nav"
            data-target="#admin-navigation"
            aria-controls="admin-navigation"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
