<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Shop;

use App\Models\Shop\Order;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * Ensure users exist to seed orders with.
     * @before
     */
    public function ensureUsersExist(): void
    {
        $this->afterApplicationCreated(fn () => User::factory(5)->create());
    }

    /**
     * Test default number creation.
     */
    public function test_empty_creation(): void
    {
        Date::setTestNow(Date::parse('May 19th, 2148'));

        $this->assertSame(
            '48.05.001',
            Order::determineOrderNumber(new Order()),
        );
    }

    /**
     * Test normal number sequencing.
     */
    public function test_order_id_generation(): void
    {
        Date::setTestNow(Date::parse('September 5th, 2009'));
        Order::factory(4)->create();

        $this->assertSame(
            '09.09.005',
            Order::determineOrderNumber(new Order()),
        );
    }

    /**
     * Test interrupted number sequencing.
     */
    public function test_interrupted_order_id_generation()
    {
        Date::setTestNow(Date::parse('August 26th, 2025'));
        Order::factory(7)->create();

        // Delete two numbers
        Order::whereIn('number', ['25.08.003', '25.08.004'])->delete();

        // Check deletion is OK
        $this->assertNull(Order::where('number', '25.08.003')->first());
        $this->assertNull(Order::where('number', '25.08.004')->first());
        $this->assertCount(5, Order::all());

        // Ensure the next number doesn't fill the freed up space
        $this->assertSame(
            '25.08.008',
            Order::determineOrderNumber(new Order()),
        );
    }
}
