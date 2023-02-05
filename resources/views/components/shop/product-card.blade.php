<?php declare(strict_types=1);
$variants = $product->variants;
$variant = optional($variants->first());

$variantPrices = $variants->map->price;

$variantLabel = sprintf('%d varianten', $variants->count());
$price = $variantPrices->min();
$priceIsMinPrice = $variantPrices->unique()->count() !== 1;

if ($variants->count() === 2) {
    $variantLabel = $variants->map->name->implode(' en ');
} elseif ($variants->count() === 1) {
    $variantLabel = $variants->first()->name;
}
?>
<div class="rounded bg-white grid grid-cols-1 w-full relative group">
    <div class="w-full min-h-64 relative">
        {{-- Image --}}
        <img src="{{ $variant->valid_image->width(360) }}" class="w-full min-h-64 object-fill group-hover:opacity-80 rounded"
            alt="Productafbeelding van {{ $product->name }}" />

        {{-- Features --}}
        @if ($product->feature_icons->isNotEmpty())
        <div class="absolute bottom-4 right-4 flex flex-row-reverse items-center mt-2">
            @foreach ($product->feature_icons as $icon => $feature)
            <div class="flex items-center ml-4 p-2 bg-white text-black rounded" title="{{ $feature }}">
                <x-icon :icon="$icon" class="h-4" />
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="flex items-start p-4">
        <div class="mr-4 flex-grow">
            <h2 class="text-xl font-title">
                <a href="{{ route('shop.product-variant', [$product->slug, $product->variants->first()->slug]) }}" class="stretched-link no-underline">
                    {{ $product->name }}
                </a>
            </h2>

            <p class="text-gray-600">
                {{ $variantLabel }}
            </p>
        </div>

        <div class="flex-none flex items-end space-x-1">
            @if ($priceIsMinPrice)
            <span class="text-sm mr-1 text-gray-600">vanaf</span>
            @endif

            <data class="text-lg" value="{{ $price }}">{{ Str::price($price) }}</data>
        </div>
    </div>
</div>
