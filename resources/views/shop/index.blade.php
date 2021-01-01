@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container mt-4 mb-8">
    <h1 class="font-title text-4xl font-bold mb-2">Gumbo Millennium Webshop</h1>
    <h2 class="font-title text-2xl text-grey-primary-1">het is geen Black Friday, maar deze shit is goed ge<i>prei</i>st.</h2>
</div>

{{-- Categories --}}
<div class="container">
    <div class="row">
        @foreach ($categories as $category)
        @php
        $firstProduct = $category->products->first();
        @endphp
        <div class="col col-12 md:col-6">
            <div class="card mb-4">
                <div class="card__figure hidden md:block" role="presentation">
                    <img class="card__figure-image" src="{{ $firstProduct->image_url }}" title="Foto van {{ $firstProduct->image_url }}">
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
</div>
@endsection
