@extends('layout.main')

@section('content')
<div class="container">
    @section('basic-content')
    <div class="container container--sm my-8">
        @yield('basic-content-small')
    </div>
    @show
</div>
@endsection
