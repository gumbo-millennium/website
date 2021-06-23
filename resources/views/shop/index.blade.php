@extends('shop.layout')

{{-- Header --}}
@section('shop-title', 'Gumbo Millennium Webshop')
@section('shop-subtitle')
    het is geen Black Friday, maar deze shit is goed ge<i>prei</i>st.
@endsection

@section('shop-adverts')
@if ($product = $advertisedProduct)
<div class="shop-header">
    <img role="none" class="shop-header-image" src="{{ $product->valid_image_url }}" />

    <div class="shop-header-gradient"></div>

    <div class="shop-header-body-wrapper">
        <div class="container shop-header-body p-4 md:p-8 md:pb-12 lg:pb-16 flex flex-col md:flex-row items-stretch">
            <div class="flex flex-col flex-grow md:justify-end">
                <p class="font-medium text-white upppercase text-xl md:text-xl lg:text-2xl md:mb-2">
                    Nieuw in de shop
                </p>

                <h3
                    class="shop-header-title font-title font-bold text-brand-primary-1 text-5xl lg:text-8xl">
                    {{ $product->default_variant->display_name }}
                </h3>
            </div>

            <div class="md:flex items-end">
                <a href="{{ $product->default_variant->url }}" class="btn btn--small btn--brand">Nu bekijken</a>
            </div>

        </div>
    </div>
</div>
@endif
@endsection

{{-- Main --}}
@section('shop-content')
<div class="row">
    @foreach ($categories as $category)
    @php($firstProduct = $category->products->first())

    <div class="col col-12 md:col-6">
        <div class="card mb-4">
            <div class="card__figure hidden md:block" role="presentation">
                <img class="card__figure-image" src="{{ $category->valid_image_url }}" title="Foto van {{ $category->valid_image_url }}">
            </div>
            <div class="card__body">
                <h2 class="card__body-title mb-0">
                    <a href="{{ route('shop.category', compact('category')) }}" class="stretched-link">
                        {{ $category->name }}
                    </a>
                </h2>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
