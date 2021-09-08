@extends('shop.layout')

{{-- Header --}}
@section('shop-title', $variant->display_name)

@section('shop-crumbs')
{{-- Breadcrumbs --}}
@breadcrumbs([
    'items' => [
        route('shop.home') => 'Shop',
        route('shop.category', compact('category')) => $category->name,
        $product->name,
    ]
])
@endbreadcrumbs
@endsection

{{-- Main --}}
@section('shop-content')
<div class="row">
    <div class="col col-12 md:col">
        <img class="w-full rounded" src="{{ $variant->valid_image_url ?? $product->valid_image_url }}"
            title="Afbeelding van {{ $product->name }}" />
    </div>

    <div class="col col-12 md:col-5 lg:col-4 mt-8 md:mt-0">
        {{-- Title --}}
        <div class="flex flex-row">
            <div class="mb-8 flex-grow">
                <h1 class="text-3xl font-title mb-4 font-bold">{{ $product->name }}</h1>
                <h2 class="text-lg font-title font-grey-2">{{ $variant->name }}</h2>
            </div>

            {{-- <div class="flex-none mt-1">
                <data class="text-2xl font-title py-2 px-4 rounded border-gray-primary-1 border mb-4 font-bold"></data>
            </div> --}}
        </div>

        {{-- Desc --}}
        <p class="mb-4 leading-relaxed">{{ $variant->description_html ?? $product->description_html }}</p>

        {{-- Variants --}}
        @if ($variants->count() > 1 )
        <h3 class="font-title font-lg mt-8 mb-2">Varianten</h3>
        <div class="shop-detail__variants">
            @foreach ($variants as $productVariant)
            <a href="{{ route('shop.product-variant', ['product' => $product, 'variant' => $productVariant]) }}"
                class="shop-detail__variant {{ $variant->is($productVariant) ? 'shop-detail__variant--active' : '' }} mb-2">
                {{ $productVariant->name }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Order button --}}
        <form action="{{ route('shop.cart.add') }}" method="POST" class="mt-8">
            @csrf
            <input type="hidden" name="variant" value="{{ $variant->id }}">
            <input type="hidden" name="quantity" value="1">

            <button class="btn btn--brand btn--wide w-full uppercase">
                @icon('solid/shopping-cart', 'h-4 mr-2')
                {{-- Start Ye' Plunder --}}
                {{ Str::price($variant->price) }}
            </button>

            <p class="text-center text-gray-primary-2">
                <strong>Let op:</strong>
                Je kan webshop aankopen alleen afhalen
            </p>
        </form>
    </div>
</div>
@endsection
