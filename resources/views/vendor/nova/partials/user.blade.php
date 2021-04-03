<dropdown-trigger class="h-9 flex items-center" slot-scope="{toggle}" :handle-click="toggle">
    <span class="text-90">
        {{ $user->alias ?? $user->first_name ?? $user->email ?? __('User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    {{-- Safe method to end one's session --}}
    <form class="hidden" id="logout_form" action="{{ route('logout') }}" method="post">
        @csrf
    </form>

    {{-- Dropdown --}}
    <ul class="list-reset">
        <li>
            <button type="submit" form="logout_form" style="width: 100%; text-align: left;" class="block no-underline text-90 hover:bg-30 p-3">
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
