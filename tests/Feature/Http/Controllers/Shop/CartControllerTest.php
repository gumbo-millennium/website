<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class CartControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestsMembersOnlyRoutes;

    public function testAddToCart(): void
    {
        $category = factory(Category::class)->create([
            'visible' => true,
        ]);

        $product = factory(Product::class)->create([
            'category_id' => $category->id,
            'visible' => true,
        ]);

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->onlyForMembers(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ], 'post');

        $response->assertRedirect(route('shop.cart'));

        $this->assertCount(1, Cart::getContent());
    }
}
