@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container mt-4 md:mt-8 mb-8">
    <h1 class="font-title text-4xl font-bold mb-2">@yield('shop-title')</h1>
    @hasSection ('shop-subtitle')
    <h2 class="font-title text-2xl text-grey-primary-1">@yield('shop-subtitle')</h2>
    @endif
</div>

{{-- Adverts? --}}
@section('shop-adverts')
@show

{{-- Breadcrumbs --}}
@section('shop-crumbs')
{{-- Breadcrumbs --}}
<x-breadcrumbs :items="[
    route('shop.home') => 'Shop',
]" />
@show

{{-- Main --}}
@section('shop-container')
<div class="container">
    @hasSection ('shop-sidebar')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="col-span-2">
            @yield('shop-content')
        </div>
        <div>
            @yield('shop-sidebar')
        </div>
    </div>
    @else
    @yield('shop-content')
    @endif
</div>
@show()
@endsection
