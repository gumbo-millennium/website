@extends('layout.main')

@php
$version = request()->getProtocolVersion() ?? 'HTTP';
@endphp

@section('content')
{{-- All in an article --}}
<article>
    {{-- Title and caption --}}
    <div class="container">
        <div class="page-hero">
            <h1 class="page-hero__title">@yield('error.title', 'Something\'s gone wrong')</h1>
            <p class="page-hero__lead">@yield('error.message', 'Sorry, please try again later')</p>
        </div>
    </div>

    <div class="container mb-4">
        @yield('error-content')
    </div>

    {{-- HTTP code --}}
    <div class="container" role="presentation">
        <p class="text-small text-gray-primary-1 text-center">Error code: {{ $version }} @yield('error.code', '500')</h2>
    </div>
</article>
@endsection
