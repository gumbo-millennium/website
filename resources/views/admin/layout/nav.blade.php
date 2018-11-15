{{-- Navigation links --}}
@php
$navLinks = [
    ['admin.home', 'Homepage'],
    // ['admin.members', 'Leden'],
    // ['admin.events', 'Evenementen'],
    ['admin.join.index', 'Aanmeldingen'],
    ['admin.files.index', trans('files.name')],
    ['admin.wordpress', 'WordPress']
];
@endphp
{{-- Show navbar --}}
<nav class="nav flex-column">
    {{-- User and logout --}}
    <div class="nav-item d-block d-sm-none">
        <div class="card my-2">
            <div class="card-body d-flex flex-nowrap align-items-center">
                <i class="fas fa-user fa-fw mr-2" aria-label="Ingelogd als"></i>
                <strong class="d-block flex-grow-1">{{ auth()->user()->name }}</strong><br />
                <button type="submit" form="logout-form" class="btn btn-brand btn-sm">
                    <span>Uitloggen</span>
                </button>
            </div>
        </div>
    </div>

    <h3 class="nav-header">Administratiepaneel</h3>

    @foreach ($navLinks as list($route, $title))
    @php
    $current = request()->routeIs($route);
    @endphp
    <a class="nav-link {{ $current ? 'active' : '' }}" href="{{ route($route) }}">
        {{ $title }}
        @if ($current)
        <span class="sr-only">(huidig)</span>
        @endif
    </a>
    @endforeach
</nav>
