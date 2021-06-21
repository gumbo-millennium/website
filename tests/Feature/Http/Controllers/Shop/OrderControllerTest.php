<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class OrderControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestsMembersOnlyRoutes;
    use TestsShop;

    public function test_get_create_with_empty_cart()
    {
        $this->onlyForMembers(route('shop.order.create'))
            ->assertRedirect(route('shop.cart'));
    }

    public function test_get_create_with_cart()
    {
        $this->markTestSkipped('Will fix when we have SQLite');

        $variant = $this->getProductVariant();

        $response = $this->onlyForMembers(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ], 'post');
        $response->assertRedirect();

        $response = $this->onlyForMembers(route('shop.order.create'));
        $response
            ->assertOk()
            ->assertSeeText($variant->display_name);
    }
}
