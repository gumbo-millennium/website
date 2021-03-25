@extends('layout.main')

@section('main.scripts', '')
@section('main.header', '')
@section('main.footer', '')

@section('content')
<div class="container container-sm payment-redirect">
    <div class="payment-redirect__logo" role="presentation">
        @event ('april-fools')
        <img src="{{ mix('images/logo-text-april-green.svg') }}" alt="Gumbo Millennium" class="payment-redirect__logo-image block dark:hidden"
            width="250" height="100" />
        <img src="{{ mix('images/logo-text-april-night.svg') }}" alt="Gumbo Millennium" class="payment-redirect__logo-image hidden dark:block"
            width="250" height="100" />
        @else
        <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="payment-redirect__logo-image block dark:hidden"
            width="250" height="100" />
        <img src="{{ mix('images/logo-text-night.svg') }}" alt="Gumbo Millennium" class="payment-redirect__logo-image hidden dark:block"
            width="250" height="100" />
        @endevent
    </div>

    <div class="payment-redirect__loading">
        <div class="payment-redirect__loading-dot"></div>
        <div class="payment-redirect__loading-dot"></div>
        <div class="payment-redirect__loading-dot"></div>
    </div>

    <p class="payment-redirect__title">@yield('redirect.title')</p>

    <p class="payment-redirect__footnote">@yield('redirect.footnote')</p>
</div>
@endsection
