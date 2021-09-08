@php
$minPrice = $product->variants->min('price');
$maxPrice = $product->variants->max('price');

$price = ($minPrice < $maxPrice ? 'v.a. ' : '') . Str::price($minPrice);
$typecount = $product->variants->count();
@endphp

<div class="card mb-4">
    <div class="card__figure relative" role="presentation">
        <img class="card__figure-image" src="{{ $product->valid_image_url }}"
            title="Foto van {{ $product->valid_image_url }}">

        {{-- Features --}}
        @if ($product->feature_icons->isNotEmpty())
        <div class="absolute bottom-4 right-4 flex flex-row-reverse items-center mt-2">
            @foreach ($product->feature_icons as $icon => $feature)
            <div class="flex items-center ml-4 p-2 bg-white text-black rounded" title="{{ $feature }}">
                @icon($icon, 'h-4')
            </div>
            @endforeach
        </div>
        @endif
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
