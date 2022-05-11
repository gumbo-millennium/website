<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class ShopCategory extends Component
{
    private Category $category;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $category = $this->category;

        /** @var Product $firstProduct */
        $firstProduct = $category
            ->products()
            ->visible()
            ->first();

        $productCount = $category->products()->visible()->count();

        return View::make('components.card', [
            'href' => route('shop.category', $category),
            'image' => $firstProduct?->image_path,
            'title' => $category->name,
            'description' => $category->description,

            'footerTitle' => trans_choice('1 product|:count products', $productCount),
        ]);
    }
}
