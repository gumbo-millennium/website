@extends('shop.layout')

{{-- Header --}}
@section('shop-title', 'Gumbo Millennium Webshop')
@section('shop-subtitle')
    Gumbo Merch tot je groen en geel ziet <span class="text-sm">(maar voornamelijk groen)</span>
@endsection

@section('shop-adverts')
@includeWhen($advertisedProduct, 'shop.partials.home-advert', ['product' => $advertisedProduct])
@endsection

{{-- Main --}}
@section('shop-content')
<div class="row">
    @forelse ($categories as $category)
    @php($firstProduct = $category->products->first())

    <div class="col col-12 md:col-6">
        <div class="card mb-4">
            <div class="card__figure hidden md:block" role="presentation">
                <img class="card__figure-image" src="{{ $category->valid_image->width(300) }}" title="Foto van {{ $category->name }}">
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
    @empty
    <div class="col-12">
        <div class="my-8 p-8 px-8 md:px-16 rounded border border-gray-300 text-gray-700 text-center font-light text-3xl">
            Er is momenteel niks beschikbaar in de webshop.
        </div>
    </div>
    @endforelse
</div>
@endsection
