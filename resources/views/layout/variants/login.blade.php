@extends('layout.main')

@section('content')

<div class="container">
    @section('login-content')
    <div class="container container--sm my-8">
        @yield('login-content-small')
    </div>
    @show
</div>
@endsection
