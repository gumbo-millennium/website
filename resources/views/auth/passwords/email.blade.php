@extends('layout.auth')

{{-- Alert --}}
@if (session('status'))
@push('auth.alert')

<div class="alert alert-success" role="alert">
    {{ session('status') }}
</div>
@endpush
@endif

@section('content')
{{-- Login text --}}
<div class="login__text login__text--before">
    <p>Vul hieronder je e-mail adres in, om je wachtwoord te herstellen.</p>
    <p>Zorg dat je direct bij je e-mail kan. De herstellink is namelijk slechts 3 uur geldig.</p>
</div>

{{-- Login form --}}
<form class="login__form" method="post" action="{{ route('password.email') }}" aria-label="{{ __('Reset Password') }}">
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

        @if ($errors->has('email'))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('email') }}</strong>
        </span>
        @endif
    </div>

    {{-- Submit button --}}
    <div class="login__form-action">
        <button class="login__form-submit" type="submit">
            {{ __('Send Password Reset Link') }}
        </button>
    </div>
</form>

{{-- Login actions --}}
<div class="login__text login__text--after">
    <p><a href="{{ route('login') }}">Terug naar inloggen</a></p>
</div>
@endsection
