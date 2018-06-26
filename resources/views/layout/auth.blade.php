@extends('layout.base')

@section('layout.content')
    {{-- The main wrapper --}}
    <div class="wrapper">
        {{-- Jump-to-content target --}}
        <div class="sr-only" id="start-of-content"></div>

        {{-- Content block --}}
        @yield('content')
    </div>
@endsection
