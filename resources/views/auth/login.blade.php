<?php
use App\Models\User;
$testUsers = App::isLocal() ? User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
$title = $seenBefore ? __('Welcome back') : __('Welcome');
?>
<x-auth-page title="Login">
  <x-sections.transparent-header
    :title="$title"
    :subtitle="$purpose"
  />

  {{-- Auto login form --}}
  @includeWhen($testUsers, 'auth.test.login', ['users' => $testUsers])

  {{-- Form --}}
  <form method="POST" action="{{ route('login') }}" class="form grid grid-cols-1 gap-4">
    @csrf

    {{-- Login e-mail --}}
    <x-input name="email" :label="__('E-mail address')" type="email" required autofocus />

    {{-- Login password --}}
    <x-input name="password" :label="__('Password')" type="password" required autocomplete="current-password" />

    {{-- Submit --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <x-button type="submit" style="primary">
        {{ __('Login') }}
      </x-button>

      <x-button href="{{ route('password.request') }}" style="outline">
        {{ __('Forgot your password?') }}
      </x-button>
    </div>

    <hr class="my-4 bg-gray-400" />

    <div>
      <h3 class="heading-3 mb-2">@lang("Don't have an account yet")</h3>
      <p>
        @lang("Register to get started.")
        @lang("Don't worry, a website-account doesn't constitute a membership.")
      </p>
    </div>

    <x-button href="{{ route('register') }}">
      {{ __('Sign Up') }}
    </x-button>
  </form>
</x-auth-page>
