<?php

declare(strict_types=1);

namespace Database\Factories\Telegram;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Telegram\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'private', 'group', 'supergroup', 'channel',
        ]);

        return [
            'chat_id' => $this->faker->numerify('###############'),
            'name' => $type == 'private' ? $this->faker->name() : $this->faker->sentence(),
        ];
    }

    public function privateChat(): self
    {
        return $this->state([
            'type' => 'private',
            'name' => $this->faker->name(),
        ]);
    }

    public function groupChat()
    {
        return $this->state([
            'type' => 'group',
            'name' => $this->faker->sentence(),
        ]);
    }

    public function supergroupChat()
    {
        return $this->state([
            'type' => 'supergroup',
            'name' => $this->faker->sentence(),
        ]);
    }

    public function channelChat()
    {
        return $this->state([
            'type' => 'channel',
            'name' => $this->faker->sentence(),
        ]);
    }
}
