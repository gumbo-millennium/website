<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Traits;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Darryldecode\Cart\Facades\CartFacade as Cart;

trait TestsShop
{
    private function getProductVariant(
        array $attributes = [],
        ?Product $product = null
    ): ProductVariant {
        if (! $product) {
            $category = factory(Category::class)->create([
                'visible' => true,
            ]);

            $product = factory(Product::class)->create([
                'category_id' => $category->id,
                'visible' => true,
            ]);
        }

        return factory(ProductVariant::class)->create(array_merge($attributes, [
            'product_id' => $product->id,
        ]));
    }

    private function assertCartQuantity(int $quantity): void
    {
        $this->assertSame(
            $quantity,
            Cart::getTotalQuantity(),
            "Total cart quantity does not match [${quantity}]"
        );
    }

    private function assertCartPrice(int $price): void
    {
        $this->assertEquals(
            $price,
            Cart::getTotal(),
            "Cart price does not match expected price of [${price}]"
        );
    }
}
