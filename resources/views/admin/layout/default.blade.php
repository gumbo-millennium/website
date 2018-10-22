@extends('layout.base')

{{--
    Include header and navigation
--}}
@section('layout.content-before')
{{-- Header --}}
{{-- Body class --}}
@push('stack.body-class')
    admin-page
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
