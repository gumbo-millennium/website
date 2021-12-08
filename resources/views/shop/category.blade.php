@extends('shop.layout')

{{-- Header --}}
@section('shop-title', $category->name)

@section('shop-crumbs')
{{-- Breadcrumbs --}}
<x-breadcrumbs :items="[
    route('shop.home') => 'Shop',
    $category->name,
]" />
@endsection

{{-- Main --}}
@section('shop-content')
<div class="row">
    @foreach ($products as $product)
    <div class="col col-12 md:col-6 lg:col-4">
        <div class="mb-4 relative">
            <x-shop.product-card :product="$product" />
        </div>
    </div>
    @endforeach
</div>
@endsection
