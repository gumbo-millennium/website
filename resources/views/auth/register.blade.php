@extends('layout.auth')

@section('content')
{{-- Login text --}}
<div class="login__text login__text--before">
    <p>
        Vul onderstaande velden in om een account aan te maken
        op de Gumbo Millennium website.
    </p>
</div>

{{-- Login form --}}
<form class="login__form" method="post" action="{{ route('register') }}" aria-label="{{ __('Registreren') }}">
    {{-- CSRF token --}}
    @csrf

    @foreach ($formFields as list($type, $name, $label, $placeholder, $help, $required))

    @endforeach
    {{-- Username field --}}
    <div class="login__form-group">
        <label class="sr-only" for="{{ $name }}">{{ $label }}</label>
        <input
        class="login__form-control{{ $errors->has($name) ? ' is-invalid' : '' }}"
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ old($name) }}"
        {{ $required ? 'required' : '' }}>
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
    <p><a href="{{ route('password.request') }}">
        {{ __('Forgot Your Password?') }}
    </a></p>
    <p>
        Nog geen lid van Gumbo Millennium? <a href="{{ route('join') }}">Meld je aan!</a>
    </p>
</div>
@endsection
