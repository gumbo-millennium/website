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

    public function testAddingItemsIsForMembersOnly(): void
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

    public function testAddToCart(): void
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
        $this->assertCartPrice(15);
    }

    public function testAddingDuplicatesGetMerged(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 6_90,
        ]);

        $safeQuantity = intdiv(Config::get('gumbo.shop.max-quantity'), 2);

        if ($safeQuantity < 1) {
            $this->markTestSkipped("Cannot properly add items, the shop max quantity is too low.");
        }

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => $safeQuantity,
        ])->assertRedirect(route('shop.cart'));

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => $safeQuantity,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(($safeQuantity + $safeQuantity) * $variant->price);
        $this->assertCartQuantity($safeQuantity + $safeQuantity);
    }

    public function testAddingDuplicatesDoesNotOverflow(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 4_20,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => Config::get('gumbo.shop.max-quantity'),
        ])->assertRedirect(route('shop.cart'));

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(Config::get('gumbo.shop.max-quantity') * $variant->price);
        $this->assertCartQuantity(Config::get('gumbo.shop.max-quantity'));
    }

    public function testUpdatingQuantityWorks(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant([
            'price' => 10_00,
        ]);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(2 * $variant->price);
        $this->assertCartQuantity(2);

        $firstItemId = Cart::getContent()->first()->id;

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => 4,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(4 * $variant->price);
        $this->assertCartQuantity(4);
    }

    public function testUpdatingToZeroWorks(): void
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

        $this->assertCartPrice(2 * $variant->price + 1 * $variant2->price);
        $this->assertCartQuantity(3);

        $firstItemId = Cart::getContent()->first()->id;

        $this->patch(route('shop.cart.update'), [
            'id' => $firstItemId,
            'quantity' => 0,
        ])->assertRedirect(route('shop.cart'));

        $this->assertCartPrice(1 * $variant2->price);
        $this->assertCartQuantity(1);
    }

    public function testAddQuantityLimits()
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant();

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 0,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 6,
        ])->assertSessionHasErrors(['quantity']);

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertSessionDoesntHaveErrors();

        $item = Cart::getContent()->first()->id;

        $this->post(route('shop.cart.add'), [
            'id' => $item,
            'quantity' => 10,
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
            'quantity' => 6,
        ])->assertSessionHasErrors(['quantity']);
    }
}
