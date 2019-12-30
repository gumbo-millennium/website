@extends('layout.main')

@section('content')

<div class="container">
    <div class="login">
        <div class="login__inner">
            @section('login-content')
            <div class="login__inner-small">
                @yield('login-content-small')
            </div>
            @show
        </div>
    </div>
</div>
@endsection
