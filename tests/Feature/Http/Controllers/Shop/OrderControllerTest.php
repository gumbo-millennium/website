<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use App\Helpers\Str;
use App\Models\Shop\Order;
use App\Models\Shop\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;
use Tests\Traits\TestsMembersOnlyRoutes;

class OrderControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestsMembersOnlyRoutes;
    use TestsShop;

    public function test_get_create_with_empty_cart(): void
    {
        $this->onlyForMembers(route('shop.order.create'))
            ->assertRedirect(route('shop.cart'));
    }

    public function test_get_create_with_cart(): void
    {
        $this->actingAs($this->getMemberUser());

        $variant = $this->getProductVariant();

        $payFee = Config::get('gumbo.transfer-fee');

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect();

        $response = $this->get(route('shop.order.create'));
        $response
            ->assertOk()
            ->assertSee($variant->display_name)
            ->assertSee(Str::price($payFee));
    }

    public function test_submit_create_with_empty_cart(): void
    {
        $this->onlyForMembers(route('shop.order.store'), [], 'post')
            ->assertRedirect(route('shop.cart'));
    }

    public function test_submit_create_with_items_in_cart(): void
    {
        $variant = $this->getProductVariant();

        $this->actingAs($this->getMemberUser());

        $variantPrice = $variant->price;
        $payFee = Config::get('gumbo.transfer-fee');

        $this->post(route('shop.cart.add'), [
            'variant' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect();

        $nextOrderId = (Order::query()->max('id') ?? 0) + 1;
        $this->post(route('shop.order.store'))
            ->assertRedirect(route('shop.order.show', [$nextOrderId]));

        $lastOrder = Order::latest()->first();

        $this->assertNotNull($lastOrder, 'Cannot find created order');

        $this->assertEquals($payFee + $variantPrice * 2, $lastOrder->price);
    }

    /**
     * Test if the index page loads when no orders are present.
     */
    public function test_show_index_empty(): void
    {
        $this->actingAs($this->getMemberUser());

        $this->get(route('shop.order.index'))
            ->assertOk();
    }

    /**
     * Test if the index page shows all orders, and works as expected.
     */
    public function test_show_full_index(): void
    {
        $user = $this->getMemberUser();
        $this->actingAs($user);

        $openOrders = Order::factory()->for($user)->count(3)->create();
        $paidOrders = Order::factory()->for($user)->count(3)->paid()->create();
        $cancelledOrders = Order::factory()->for($user)->count(3)->cancelled()->create();
        $expiredOrders = Order::factory()->for($user)->count(3)->expired()->create();

        $response = $this->get(route('shop.order.index'))
            ->assertOk();

        $allOrders = Collection::make()
            ->merge($openOrders)
            ->merge($paidOrders)
            ->merge($cancelledOrders)
            ->merge($expiredOrders);

        foreach ($allOrders as $order) {
            $response->assertSee($order->number);
        }
    }

    public function test_view_order(): void
    {
        $memberUser = $this->getMemberUser();

        $variant = $this->getProductVariant();

        $order = $this->createOrder($memberUser, [
            $variant,
        ]);

        $orderUrl = route('shop.order.show', [$order]);

        // Test logged out
        $this->get($orderUrl)
            ->assertRedirect(route('login'));

        // Test guest (members only)
        $this->actingAs($this->getGuestUser());
        $this->get($orderUrl)
            ->assertForbidden();

        // Test different user than owner
        $this->actingAs($this->getBoardUser());
        $this->get($orderUrl)
            ->assertNotFound();

        // Test actual user
        $this->actingAs($memberUser);
        $this->get($orderUrl);

        // Test index route
        $this->actingAs($memberUser);
        $this->get(route('shop.order.index'))
            ->assertSee($order->number);
    }

    /**
     * Create a quick order to test with.
     */
    private function createOrder(User $user, array $variants, int $fee = 100): Order
    {
        // Map variants to array
        $variants = collect($variants);

        // Create order
        $order = new Order([
            'fee' => $fee,
            'price' => $variants->sum('price') + $fee,
        ]);

        $order->user()->associate($user);

        // Save changes
        $order->save();

        // Assign variants, mapped as a proper table
        $variantWithAmount = collect($variants)
            ->mapWithKeys(fn (ProductVariant $variant) => [
                $variant->id => [
                    'price' => $variant->price,
                    'quantity' => 1,
                ],
            ]);

        $order->variants()->sync($variantWithAmount);

        $order
            ->refresh()
            ->loadMissing([
                'variants',
                'variants.product',
            ]);

        return $order;
    }
}
