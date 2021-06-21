@extends('shop.layout')

{{-- Header --}}
@section('shop-title', 'Gumbo Millennium Webshop')
@section('shop-subtitle')
    het is geen Black Friday, maar deze shit is goed ge<i>prei</i>st.
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
