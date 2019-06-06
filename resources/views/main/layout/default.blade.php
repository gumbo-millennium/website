@extends('main.layout.base')

{{--
    Include header and navigation
--}}
@section('layout.content-before')
{{-- Header --}}
@include('main.layout.header')

{{-- Include header stack --}}
@stack('stack.header')
@endsection

{{--
    Content section
--}}
@section('layout.content')
{{-- Include pre content stack --}}
@stack('stack.before')

{{-- Include content --}}
@yield('content')

{{-- Include post content stack --}}
@stack('stack.after')
@endsection

{{--
    Footer section
--}}
@section('layout.content-after')
{{-- Include footer stack --}}
@stack('stack.footer')

{{-- Footer, including image --}}
@include('main.layout.footer')
@endsection
