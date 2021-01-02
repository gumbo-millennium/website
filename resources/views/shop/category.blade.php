@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container mt-4 mb-8">
    <h1 class="font-title text-4xl font-bold">{{ $category->name }}</h1>
</div>

{{-- Products --}}
<div class="container">
    <div class="row">
        @foreach ($products as $product)
        @php
        $minPrice = $product->variants->min('price');
        $maxPrice = $product->variants->max('price');

        $price = ($minPrice < $maxPrice ? 'v.a. ' : '') . Str::price($minPrice);
        $typecount = $product->variants->count();
        @endphp
        <div class="col col-12 md:col-6 lg:col-4">
            <div class="mb-4 relative">
                <div class="card mb-4">
                    <div class="card__figure" role="presentation">
                        <img class="card__figure-image"
                            src="{{ $product->image_url }}"
                            title="Foto van {{ $product->image_url }}">
                    </div>
                    <div class="card__body">
                        <h2 class="card__body-title">
                            <a href="{{ route('shop.product', compact('product')) }}" class="stretched-link text-lg font-title">
                                {{ $product->name }}
                            </a>
                        </h2>

                        <div class="card__body-meta card__list">
                            @if ($typecount > 1)
                            <div>{{ $typecount }} varianten</div>
                            <div class="card__list-separator">&bull;</div>
                            @endif

                            <div>{{ $price }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
