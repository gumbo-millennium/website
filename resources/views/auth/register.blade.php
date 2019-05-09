@extends('main.layout.auth')

{{-- Change page title --}}
@section('title')
Registreren - Gumbo Millennium
@endsection

@php
$formFields = [
    ['text', 'first_name', 'Voornaam', true, null],
    ['text', 'insert', 'Tussenvoegsel', false, null],
    ['text', 'last_name', 'Achternaam', true, null],
    ['email', 'email', 'E-mail adres', true, 'mt-2'],
    ['password', 'password', 'Wachtwoord', true, 'mt-2'],
    ['password', 'password_confirmation', 'Wachtwoord herhalen', true, null]
];
@endphp

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

    @foreach ($formFields as list($type, $name, $label, $required, $className))
    <div class="login__form-group {{ $className ?? '' }}">
        <label class="sr-only" for="{{ $name }}">{{ $label }}</label>
        <input
        class="login__form-control{{ $errors->has($name) ? ' is-invalid' : '' }}"
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $label }}"
        value="{{ old($name) }}"
        {{ $required ? 'required' : '' }}>

        @if ($errors->has($name))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first($name) }}</strong>
        </span>
        @endif
    </div>
    @endforeach

    {{-- Submit button --}}
    <div class="login__form-action">
        <button class="login__form-submit" type="submit">
            Registreren
        </button>
    </div>
</form>

{{-- Login actions --}}
<div class="login__text login__text--after">
    <p>
        <a href="{{ route('login') }}">Terug naar inloggen</a>
    </p>
</div>
@endsection
