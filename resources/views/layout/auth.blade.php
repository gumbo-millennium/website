@extends('layout.base')

@push('stack.body-class')
page--login
@endpush

@section('layout.content-before')
{{-- The main wrapper --}}
<div class="login">
    {{-- Header --}}
    <div class="login__header">
        <a class="login__header-brand" href="/">
            <img src="/svg/logo-text.svg" class="login__header-brand-image" alt="" aria-labelledby="header-brand-text" />
            <span class="sr-only" id="header-brand-text">Gumbo Millennium</span>
        </a>
    </div>
@endsection

@section('layout.content')
    {{-- Alert messages --}}
    <div class="login__alerts">
        @stack('auth.alert')
        {{-- Handles alerts --}}
        @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
        @elseif (isset($errors) && $errors->count())
        <div class="alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
        @endif
    </div>

    {{-- Content block --}}
    @yield('content')
@endsection

@section('layout.content-after')
</div>
@endsection
