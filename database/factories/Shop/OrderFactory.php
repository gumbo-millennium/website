<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var null|string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'price' => $this->faker->numberBetween(150, 15000),
        ];
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->for(User::factory());
    }

    /**
     * The order has been paid.
     */
    public function paid(): self
    {
        return $this->state([
            'paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * The order has expired.
     */
    public function expired(): self
    {
        return $this->state([
            'expires_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }

    /**
     * The order has been cancelled.
     */
    public function cancelled(): self
    {
        return $this->state([
            'cancelled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
