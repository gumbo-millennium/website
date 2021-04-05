<dropdown-trigger class="h-9 flex items-center">
    <span class="text-90">
        {{ $user->alias ?? $user->first_name ?? $user->email ?? __('User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    {{-- Safe method to end one's session --}}
    <form class="hidden" id="nova-logout-form" action="{{ route('logout') }}" method="post">
        @csrf
    </form>

    {{-- Dropdown --}}
    <ul class="list-reset">
        <li>
            <a href="{{ route('home') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Homepage') }}
            </a>
        </li>
        <li>
            <a href="{{ route('account.index') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('My Account') }}
            </a>
        </li>
        <li>
            <button type="submit" form="nova-logout-form" style="appearance: none; width: 100%; text-align: left;" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </button>
        </li>
        <li>
            <nova-dark-theme-toggle
                label="{{ __('Dark Theme') }}"
            ></nova-dark-theme-toggle>
        </li>
    </ul>
</dropdown-menu>
