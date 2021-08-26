<div class="container">
    <div class="home-hero">
        <div class="home-hero__text">
            <h2 class="home-hero__title">
                {{ $leadTop }}<br />
                <span class="home-hero__title-fat">{{ $leadBig }}</span>
            </h2>

            <p class="home-hero__lead">Welkom bij de gezelligste studentenvereniging van Zwolle.</p>

            <div class="flex flex-row">
                <a href="{{ route('join.form') }}" class="btn btn--brand my-0">Word lid</a>
                @if ($user && $user->is_member)
                    <a href="{{ route('files.index') }}" class="btn btn--link my-0 ml-4">Naar documenten</a>
                @endif
            </div>
        </div>

        <div class="home-hero__logo">
            <img src="{{ mix('images/logo-glass-green.svg') }}" alt="Gumbo Millennium"
                class="home-hero__logo-image hidden dark:block" />
            <img src="{{ mix('images/logo-glass-night.svg') }}" alt="Gumbo Millennium"
                class="home-hero__logo-image block dark:hidden" />
        </div>
    </div>
</div>
