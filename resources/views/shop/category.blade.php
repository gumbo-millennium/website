@extends('shop.layout')

{{-- Header --}}
@section('shop-title', $category->name)

@section('shop-crumbs')
{{-- Breadcrumbs --}}
@breadcrumbs([
    'items' => [
        route('shop.home') => 'Shop',
        $category->name,
    ]
])
@endbreadcrumbs
@endsection

{{-- Main --}}
@section('shop-content')
<div class="row">
    @foreach ($products as $product)
    <div class="col col-12 md:col-6 lg:col-4">
        <div class="mb-4 relative">
            @component('components.shop-item', ['product' => $product])
            @endcomponent
        </div>
    </div>
    @endforeach
</div>
@endsection
