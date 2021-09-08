<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class CartControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestsMembersOnlyRoutes;
    use TestsShop;

    private const SHOP_FEE = 50;

    private const SHOP_ORDER_LIMIT = 6;

    /**
     * @before
     */
    public function ensureConsistentConfig(): void
    {
        $this->afterApplicationCreated(fn () => Config::set([
            'gumbo.transfer-fee', self::SHOP_FEE,
            'gumbo.shop.order-limit' => self::SHOP_ORDER_LIMIT,
        ]));
    }

    public function test_adding_items_is_for_members_only(): void
    {
        $this->onlyForMembers(route('shop.cart'))
            ->assertOk();

        $variant = $this->getProductVariant([
            'price' => 15,
        ]);

        $this->onlyForMembers(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ], 'post')->assertRedirect(route('shop.cart'));

        $this->onlyForMembers(route('shop.cart.update'), [
            'id' => $variant->id,
            'quantity' => 1,
        ], 'patch')->assertRedirect(route('shop.cart'));
    }

    public function test_add_to_cart(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 15,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartQuantity(1);
        $this->assertCartPrice(15 + self::SHOP_FEE);
    }

    public function test_adding_duplicates_get_merged(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 6_90,
        ]);

        $safeQuantity = (int) (self::SHOP_ORDER_LIMIT / 2);

        if ($safeQuantity < 1) {
            $this->markTestSkipped('Cannot properly add items, the shop max quantity is too low.');
        }

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => $safeQuantity,
        ])->assertRedirect(route('shop.cart'));

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => $safeQuantity,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(($safeQuantity + $safeQuantity) * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity($safeQuantity + $safeQuantity);
    }

    public function test_adding_duplicates_does_not_overflow(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 4_20,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => self::SHOP_ORDER_LIMIT,
        ])->assertRedirect(route('shop.cart'));

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(self::SHOP_ORDER_LIMIT * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(self::SHOP_ORDER_LIMIT);
    }

    public function test_updating_quantity_works(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 10_00,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(2 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(2);

        $firstItemId = Cart::getContent()->first()->id;

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => 4,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(4 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(4);
    }

    public function test_updating_to_zero_works(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 10_00,
        ]);

        $variant2 = $this->getProductVariant([
            'price' => 15_00,
        ], $variant->product);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect(route('shop.cart'));

        $this->post(route('shop.cart.add'), [
            'variant' => $variant2->id,
            'quantity' => 1,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(2 * $variant->price + 1 * $variant2->price + self::SHOP_FEE);
        $this->assertCartQuantity(3);

        $firstItemId = Cart::getContent()->first()->id;

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => 0,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(1 * $variant2->price + self::SHOP_FEE);
        $this->assertCartQuantity(1);
    }

    public function test_add_quantity_limits()
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant();

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 0,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => self::SHOP_ORDER_LIMIT + 1,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertSessionDoesntHaveErrors();

        $item = Cart::getContent()->first()->id;

        $this->post(route('shop.cart.add'), [
            'id' => $item,
            'quantity' => self::SHOP_ORDER_LIMIT * 50,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'id' => $item,
            'quantity' => -100,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'id' => $item,
            'quantity' => null,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => self::SHOP_ORDER_LIMIT + 1,
        ])->assertSessionHasErrors(['quantity']);
    }
}
