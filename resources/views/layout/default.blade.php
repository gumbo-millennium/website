@extends('layout.base')

@section('layout.content-before')
{{-- Header --}}
    @include('layout.header')
@endsection

@section('layout.content')
    @yield('content')
@endsection

@section('layout.content-after')
    {{-- Footer, including image --}}
    @include('layout.footer')

    {{-- Back to top button --}}
    <a class="scroll-top" href="#top"><i class="fa fa-angle-up"></i></a>
@endsection
