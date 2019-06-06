@php
$user = auth()->user();
@endphp
<div class="userbar">
    <ul class="nav userbar__nav">
        @auth
        {{-- Show the user's name --}}
        <li class="nav-item userbar__nav-item userbar__nav-item--user">
            <div class="nav-link">
                Hallo {{ $user->name }}
            </div>
        </li>

        {{-- Render join button if NOT a member --}}
        @unlessrole('member')
        <li class="nav-item userbar__nav-item">
            <a class="nav-link userbar__nav-link userbar__list-link--join" href="{{ route('join.form') }}">
                Word lid
            </a>
        </li>
        @endunlessrole

        {{-- Render admin link if allowed --}}
        @can('admin')
        <li class="nav-item userbar__nav-item">
            <a class="nav-link userbar__nav-link" href="{{ route('admin.home') }}">
                Admin
            </a>
        </li>
        @endcan

        {{-- Add logout link --}}
        <li class="nav-item userbar__nav-item">
            <a class="nav-link userbar__nav-link" href="{{ route('logout') }}" data-action="submit-form" data-target="navbar-logout-form">
                Uitloggen
            </a>
        </li>
        @else
        <li class="nav-item userbar__nav-item">
            <a class="nav-link userbar__nav-link userbar__nav-link--join" href="{{ route('join.form') }}">
                Word lid
            </a>
        </li>
        <li class="nav-item userbar__nav-item">
            <a class="nav-link userbar__nav-link" href="{{ route('login') }}">
                Registreren
            </a>
        </li>
        <li class="nav-item userbar__nav-item">
            <a class="nav-link userbar__nav-link" href="{{ route('login') }}">
                Inloggen
            </a>
        </li>
        @endauth
    </ul>
</div>
