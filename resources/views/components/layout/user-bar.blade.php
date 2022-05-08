@push('header.userbar-class', 'userbar')
<div class="@stack('header.userbar-class')" role="complementary">
    <div class="container userbar__container">
        {{-- Contact details --}}
        <ul class="userbar__links userbar__links--contact">
            <!--sse-->
            <li class="userbar__links-link">
                <a href="tel:+31388450100" class="userbar__links-item userbar__links-item--flex">
                    <x-icon icon="solid/phone" />
                    <span>038 845 0100</span>
                </a>
            </li>
            <li class="userbar__links-link">
                <a href="https://wa.me/31388450100" target="_blank" rel="nofollow noopener noreferer"
                    class="userbar__links-item userbar__links-item--flex">
                    <x-icon icon="brands/whatsapp" />
                </a>
            </li>
            <li class="userbar__links-link">
                <a href="mailto:bestuur@gumbo-millennium.nl" class="userbar__links-item userbar__links-item--flex">
                    <x-icon icon="solid/envelope" />
                    <span>bestuur@gumbo-millennium.nl</span>
                </a>
            </li>
            <!--/sse-->
        </ul>

        {{-- User info --}}
        @unless ($lustrumNav ?? false)
        <ul class="userbar__links userbar__links--user">
            @auth
            {{-- Shopping cart --}}
            @unless (Cart::isEmpty())
            <li class="userbar__links-link cursor-default">
                <a href="{{ route('shop.cart') }}" class="userbar__links-item" aria-label="Bekijk je winkelwagentje">
                    <x-icon icon="solid/shopping-cart" />
                    <span>
                        {{ Lang::choice('1 product|:count products', Cart::getContent()->sum('quantity')) }}
                    </span>
                </a>
            </li>
            @endif

            {{-- User name --}}
            <li class="userbar__links-link cursor-default">
                <a href="{{ route('account.index') }}" class="userbar__links-item">
                    <x-icon icon="solid/user" />
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
        @endunless
    </div>
</div>

