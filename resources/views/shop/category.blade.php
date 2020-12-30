@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">"{{ $category->name }}" categorie</h1>
        <p class="page-hero__lead">
            Merch? We got 'em
        </p>
    </div>
</div>

{{-- Products --}}
<div class="container">
    <div class="flex flex-row flex-wrap">
        @foreach ($products as $product)
        <div class="w-full md:w-1/2 lg:w-1/3 p-4">
            <a href="{{ route('shop.product', compact('product')) }}">
                {{ $product->name }}
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
