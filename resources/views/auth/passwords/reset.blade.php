@extends('main.layout.auth')

{{-- Change page title --}}
@section('title')
Wachtwoord herstellen - Gumbo Millennium
@endsection

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
    <p>Vul hieronder ter controle je e-mail adres in, en daarna 2x je nieuwe wachtwoord.</p>
    <p>Daarna zal je wachtwoord gewijzigd worden.</p>
</div>

{{-- Login form --}}
<form class="login__form" method="post" action="{{ route('password.request') }}" aria-label="{{ __('Reset Password') }}">
    {{-- CSRF token --}}
    @csrf

    {{-- Reset token --}}
    <input type="hidden" name="token" value="{{ $token }}">

    {{-- Username field --}}
    <div class="login__form-group">
        <input
        class="login__form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
        type="email"
        name="email"
        placeholder="{{ __('E-mail adres') }}"
        value="{{ old('email') }}"
        autofocus
        required>

        @if ($errors->has('email'))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('email') }}</strong>
        </span>
        @endif
    </div>

    {{-- Password field --}}
    <div class="login__form-group">
        <input
        class="login__form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
        type="password"
        name="password"
        placeholder="{{ __('Wachtwoord') }}"
        required>

        @if ($errors->has('password'))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
        @endif
    </div>

    {{-- Password confirmation field --}}
    <div class="login__form-group">
        <input
        class="login__form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
        type="password"
        name="password_confirmation"
        placeholder="Bevestig wachtwoord"
        required>
    </div>

    {{-- Submit button --}}
    <div class="login__form-action">
        <button class="login__form-submit" type="submit">
            Wachtwoord opnieuw instellen
        </button>
    </div>
</form>
@endsection
