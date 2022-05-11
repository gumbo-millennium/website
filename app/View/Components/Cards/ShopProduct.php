<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Helpers\Str;
use App\Models\Shop\Product;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class ShopProduct extends Component
{
    private Product $product;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $product = $this->product;

        $variants = $product->variants;
        $variantPrices = $variants->pluck('price');

        // Determine price label
        $price = $variantPrices->min();
        $hasMultiplePrices = $variantPrices->unique()->count() !== 1;

        // Determine variant label
        $variantLabel = trans_choice('1 variant|:count variants', $variants->count());

        // Done
        return View::make('components.card', [
            'href' => $product->url,
            'image' => $product->image_path,
            'title' => $product->name,
            'lead' => $product->category->name,

            'footerTitle' => $variantLabel,
            'footerText' => $hasMultiplePrices
                ? __('From :price', ['price' => Str::price($price)])
                : Str::price($price),
        ]);
    }
}
