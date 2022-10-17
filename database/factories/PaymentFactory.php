<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'provider' => 'test',
            'transaction_id' => $this->faker->uuid,
            'price' => $this->faker->numberBetween(1_00, 250_00),
        ];
    }

    public function subject(Model $subject): self
    {
        return $this->afterMaking(fn (Payment $payment) => $payment->payable()->associate($subject));
    }

    public function paid(): self
    {
        return $this->state([
            'paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function expired(): self
    {
        return $this->state([
            'expired_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state([
            'cancelled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
