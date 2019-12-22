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
                <a class="navbar__nav-link" href="/activities">Activities</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/files">Files</a>
            </li>
            <li class="navbar__nav-item">
                <a class="navbar__nav-link" href="/news">News</a>
            </li>
        </ul>
    </div>
</nav>
