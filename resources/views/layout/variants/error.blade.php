@extends('layout.main')

@php
$version = request()->getProtocolVersion() ?? 'HTTP';
@endphp

@section('content')
<div class="container">
    {{-- Title and caption --}}
    <div class="my-8 p-8 md:mb-4">
        <h1 class="text-4xl text-brand-600 font-bold text-center mb-8">@yield('error.title', 'Something\'s gone wrong')</h1>
        <p class="text-lg text-center text-gray-700">@yield('error.message', 'Sorry, please try again later')</p>
    </div>

    {{-- HTTP code --}}
    <div class="hidden md:block md:px-16 md:mt-16" role="presentation">
        <p class="text-small text-gray-600 text-center">Error code: {{ $version }} @yield('error.code', '500')</h2>
    </div>
</div>
@endsection
