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

{{-- Banner for messages --}}
@if (session('status'))
<div class="alert alert-info">
    {!! session('status') !!}
</div>
@endif

{{-- Banner for errors --}}
@if ($errors->any())
<div class="alert alert-waring">
    @section('validation.error')
    <p>Er ging iets fout bij het laatste verzoek.</p>
    @show
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Main content --}}
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
