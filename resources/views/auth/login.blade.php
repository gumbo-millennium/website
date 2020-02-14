@extends('layout.variants.login')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Welkom <strong class="login__title-fat">terug</strong></h1>
<p class="login__subtitle">Log in om je aan te melden voor activiteiten.</p>

{{-- Auto login form --}}
@includeWhen($testUsers, 'auth.test.login', ['users' => $testUsers])

{{-- Form --}}
<form method="POST" action="{{ route('login') }}" class="login__form">
    @csrf

    {{-- Login e-mail --}}
    <div class="form__field">
        {{-- Label --}}
        <label for="email" class="form__field-label form__field-label--required">E-mailadres</label>

        {{-- Input --}}
        <input id="email" type="email" class="form__field-input form-input" name="email"
            value="{{ old('email') }}" required autocomplete="email" autofocus>

        {{-- Error --}}
        @error('email')
        <div class="form__field-error" role="alert">
            <strong>{{ $message }}</strong>
        </div>
        @enderror
    </div>

    {{-- Login password --}}
    <div class="login__field mb-4">
        {{-- Label --}}
        <div class="flex flex-row items-center">
            <label for="password" class="form__field-label form__field-label--required flex-grow">Wachtwoord</label>
            <a class="text-sm" href="{{ route('password.request') }}">Wachtwoord vergeten?</a>
        </div>

        {{-- Input --}}
        <input id="password" type="password" class="form__field-input form-input" name="password"
            value="{{ old('password') }}" required autocomplete="current-password">

        {{-- Error --}}
        @error('password')
        <div class="form__field-error" role="alert">
            <strong>{{ $message }}</strong>
        </div>
        @enderror
    </div>

    {{-- Don't you, forget about me --}}
    <div class="form__field form__field--checkbox">
        <input class="form__field-input form__field-input--checkbox form-checkbox" id="remember" name="remember"
            type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
        <label for="remember_me" class="form__field-label">Blijf ingelogd</label>
    </div>

    <div class="sm:flex flex-row items-center">
        {{-- Register links --}}
        <p class="m-0 text-sm flex-shrink-0 flex flex-row">
            <span class="mr-1">Nog geen account?</span>
            <a class="login__link block flex-shrink-0" href="{{ route('register') }}">Aanmelden</a>
        </p>

        {{-- Spacing --}}
        <div class="flex-grow mr-8"></div>
        {{-- Submit button --}}
        <button class="form__field-input form-input flex-grow inline-block w-auto" type="submit">Inloggen</button>
    </div>
</form>
@endsection
