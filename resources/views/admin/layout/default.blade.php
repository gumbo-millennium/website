@extends('layout.base')

@php
$layout_deferStartOfContent = true;
@endphp

{{-- Change title --}}
@section('title')
    Administratie – Gumbo Millennium
@endsection


{{-- Add CSRF token to meta queue --}}
@push('stack.meta-tags')
    <meta name="laravel-csrf-token" content="{{ csrf_token() }}" />
@endpush

{{--
    Include header and navigation
--}}
@section('layout.content-before')
{{-- Header --}}
{{-- Body class --}}
@push('stack.body-class')
    admin
@endpush

{{-- Header --}}
@include('admin.layout.header')

{{-- Logout form --}}
<form class="d-none" action="{{ route('logout') }}" method="POST" id="logout-form">
    @csrf
</form>

{{-- Header stack --}}
@stack('stack.header')
@endsection

@section('a10y.start-of-content')
{{-- No content here --}}
@endsection

{{-- Content section --}}
@section('layout.content')
<div class="container admin-container">
    <div class="admin-row">
        <nav class="admin-sidenav" id="admin-navigation">
            @include('admin.layout.nav')
        </nav>
        <main class="admin-content">
            {{-- Show start-of-content --}}
            <div class="sr-only sr-start-of-content" id="start-of-content"></div>

            {{-- Show error messagess --}}
            @include('admin.layout.before-content')

            {{-- Main content --}}
            @yield('content')
        </main>
    </div>
</div>
@endsection

{{-- Footer --}}
@section('layout.content-after')
@stack('stack.footer')
@include('admin.layout.footer')
@endsection

@push('stack.scripts')
<script src="{{ mix('/gumbo-admin.js') }}"></script>
@endpush
