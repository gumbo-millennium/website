@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <ul class="flex flex-row items-center my-4">
        <li class="mr-2"><a href="{{ route('shop.home') }}">Shop</a></li>
        <li class="mr-2"><a href="{{ route('shop.category', ['category' => $category]) }}">{{ $category->name }}</a></li>
        <li class="mr-2"><a href="{{ route('shop.product-variant', compact('product', 'variant')) }}">{{ $product->name }}</a></li>
    </ul>
</div>

{{-- Products --}}
<div class="container flex flex-row">
    <div class="flex-none w-3/4 px-8 py-4">
        <img src="{{ $variant->image_url ?? $product->image_url }}" alt="Afbeelding van {{ $product->name }}" class="w-full" />
    </div>

    <div class="flex-none w-1/4">
        <div class="mb-8">
            <h1 class="text-3xl font-title mb-4 font-bold">{{ $product->name }}</h1>
            <h2 class="text-lg font-title font-grey-2">{{ $variant->name }}</h2>
        </div>

        <div class="flex flex-col items-stretch">
            @foreach ($variants as $variant)
            <a href="{{ route('shop.product-variant', compact('product', 'variant')) }}" class="p-4 mb-2 rounded bg-grey-1 border border-brand-600">
                {{ $variant->name }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
