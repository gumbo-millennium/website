<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class CartControllerTest extends TestCase
{
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
            'gumbo.transfer-fee' => self::SHOP_FEE,
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

        $this->assertCartPrice(self::SHOP_ORDER_LIMIT * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(self::SHOP_ORDER_LIMIT);

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
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(2 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(2);

        $firstItemId = Cart::getContent()->first()->id;

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => 4,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(4 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(4);

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => self::SHOP_ORDER_LIMIT + 2,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(self::SHOP_ORDER_LIMIT * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(self::SHOP_ORDER_LIMIT);
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
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->post(route('shop.cart.add'), [
            'variant' => $variant2->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(2 * $variant->price + 1 * $variant2->price + self::SHOP_FEE);
        $this->assertCartQuantity(3);

        $firstItemId = Cart::getContent()->first()->id;

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => 0,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

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
        ])->assertSessionHasNoErrors();

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $item = Cart::getContent()->first()->id;

        $this->post(route('shop.cart.add'), [
            'id' => $item,
            'quantity' => self::SHOP_ORDER_LIMIT * 50,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

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
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));
    }

    public function test_increased_custom_order_limits(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 10_00,
            'order_limit' => 10,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 10,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(10 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(10);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 11,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertSame(10, Cart::getContent()->first()->quantity);
    }

    public function test_decreased_order_limit(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 10_00,
            'order_limit' => 3,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 3,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(3 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(3);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 4,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertSame(3, Cart::getContent()->first()->quantity);
    }

    public function test_local_order_limit(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 10_00,
            'order_limit' => 3,
        ]);

        $variant->product->order_limit = 10;
        $variant->product->save();

        $variant2 = $this->getProductVariant([
            'price' => 5_00,
        ], $variant->product);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 3,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(3 * $variant->price + self::SHOP_FEE);
        $this->assertCartQuantity(3);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant2->id,
            'quantity' => 6,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(3 * $variant->price + 6 * $variant2->price + self::SHOP_FEE);
        $this->assertCartQuantity(3 + 6);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 4,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        // 3 + 6 instead of 4 + 6
        $this->assertCartQuantity(3 + 6);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant2->id,
            'quantity' => 16,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('shop.cart'));

        // 3 + 10, instead of 3 + 16
        $this->assertCartQuantity(3 + 10);
    }
}
