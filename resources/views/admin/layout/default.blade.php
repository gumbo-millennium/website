@extends('layout.base')

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
    body-admin
@endpush

{{-- Header --}}
@include('admin.layout.header')
@stack('stack.header')
@endsection


{{-- Content section --}}
@section('layout.content')
<main role="main" class="container admin-container">
@yield('content')
</main>
@endsection

{{-- Footer --}}
@section('layout.content-after')
@stack('stack.footer')
@include('admin.layout.footer')
@endsection

@push('stack.scripts')
<script src="{{ mix('/gumbo-admin.js') }}"></script>
@endpush
