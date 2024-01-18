<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BotQuote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BotQuoteReaction>
 */
class BotQuoteReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reaction' => $this->faker->randomElement(['👍', '👎']),
        ];
    }

    public function configure(): self
    {
        return $this
            ->has(BotQuote::factory(), 'quote')
            ->has(User::factory(), 'user');
    }
}
