@extends('layout.variants.login')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('login-content')
{{-- Header --}}
<h1 class="login__header text-4xl">{{ __('Sign Up') }}</h1>

{{-- Render form --}}
{!! form($form, ['class' => 'login__form']) !!}
@endsection
