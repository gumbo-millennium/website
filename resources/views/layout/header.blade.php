<nav class="navbar">
    <div class="container navbar__container">
        <a href="{{ route('home') }}" class="logo-wrapper">
            <img src="{{ mix('/images/logo-text-green.svg') }}" alt="Gumbo Millennium" aria-label="Logo Gumbo Millennium"
                class="logo" width="160" height="64" />
        </a>
        <div class="navbar__filler"></div>
        <ul class="navbar__nav">
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/">Home</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/about">Over</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/activities">Activiteiten</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/files">Bestanden</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/news">Nieuws</a>
            </li>
        </ul>
    </div>
</nav>
