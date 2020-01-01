@extends('layout.variants.login')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('login-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Welkom <strong class="font-bold">terug</strong></h1>

{{-- Auto login form --}}
@includeWhen($testUsers, 'auth.test.login', ['users' => $testUsers])

{{-- Form --}}
<div class="card">

    <form method="POST" action="{{ route('login') }}" class="login__form">
        @csrf

        {{-- Login e-mail --}}
        <div class="mb-4 login__field">
            {{-- Label --}}
            <label for="email" class="login__field-label block text-sm mb-2">{{ __('E-Mail Address') }}</label>

            {{-- Input --}}
            <input id="email" type="email" class="login__field-input form-input block" name="email"
                value="{{ old('email') }}" required autocomplete="email" autofocus>

            {{-- Error --}}
            @error('email')
            <div class="mt-2 login__field-error" class="text-red-700" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        {{-- Login password --}}
        <div class="login__field mb-4">
            {{-- Label --}}
            <label for="password" class="login__field-label block text-sm mb-2">{{ __('Password') }}</label>

            {{-- Input --}}
            <input id="password" type="password" class="login__field-input form-input block" name="password"
                value="{{ old('password') }}" required autocomplete="current-password">

            {{-- Error --}}
            @error('password')
            <div class="mt-2 login__field-error" class="text-red-700" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        {{-- Don't you, forget about me --}}
        <div class="mb-4 flex flex-row login__field login__field--checkbox">
            <input class="form-checkbox" type="checkbox" name="remember" id="remember"
                {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label flex-grow ml-2" for="remember">Blijf ingelogd</label>
        </div>

        {{-- Submit button --}}
        <button class="login__submit block btn btn-brand mb-4" type="submit">Inloggen</button>

        {{-- Forgot Password and Register --}}
        <div class="login__links">
            <a class="login__link" href="{{ route('password.request') }}">Wachtwoord vergeten?</a>
            <a class="login__link" href="{{ route('register') }}">Nog geen account?</a>
        </div>
    </form>
</div>
@endsection
