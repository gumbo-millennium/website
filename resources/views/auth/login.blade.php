@extends('layout.auth')

{{-- Change page title --}}
@section('title')
Inloggen - Gumbo Millennium
@endsection


{{-- Add notice --}}
@push('auth.alert')
<div class="alert alert-info" role="alert">
    Onderstaand inlogscherm is voor toegang tot besloten systemen.
    Je hebt het niet nodig voor algemeen gebruik van de website.
</div>
@endpush


@section('content')
{{-- Login text --}}
<div class="login__text login__text--before">
    <p>
        Vul hieronder je e-mail adres en wachtwoord in
        om in te loggen op de website.
    </p>
</div>

{{-- Login form --}}
<form class="login__form" method="post" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
    {{-- CSRF token --}}
    @csrf

    {{-- Username field --}}
    <div class="login__form-group">
        <input
        class="login__form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
        type="email"
        name="email"
        placeholder="bestuur@gumbo-millennium.nl"
        value="{{ old('email') }}"
        required>
    </div>

    {{-- Password field --}}
    <div class="login__form-group">
        <input
        class="login__form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
        type="password"
        name="password"
        placeholder="wachtwoord"
        required>

        @if ($errors->has('password'))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
        @endif
    </div>

    {{-- Remember me --}}
    <div class="login__form-group login__form-checkbox custom-control custom-checkbox">
        <input class="custom-control-input login__form-checkbox-input" type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }} />
        <label class="custom-control-label login__form-checkbox-label" for="remember-me">
            {{ __('Remember Me') }}
        </label>
    </div>

    {{-- Submit button --}}
    <div class="login__form-action">
        <button class="login__form-submit" type="submit">
            {{ __('Login') }}
        </button>
    </div>
</form>

{{-- Login actions --}}
<div class="login__text login__text--after">
    <p>
        <a href="{{ route('password.request') }}">
            Wachtwoord vergeten?
        </a>
    </p>
    <p>
        Heb je nog geen account? <a href="{{ route('register') }}">Maak er een aan</a>.
    </p>
</div>
@endsection
