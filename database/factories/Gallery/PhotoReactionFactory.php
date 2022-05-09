<?php

declare(strict_types=1);

namespace Database\Factories\Gallery;

use App\Enums\PhotoReactionType;
use App\Models\Gallery\Photo;
use App\Models\Gallery\PhotoReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;

        return [
            'reaction' => $faker->randomElement(PhotoReactionType::cases())->value,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (PhotoReaction $reaction) {
            if ($reaction->user === null) {
                $reaction->user()->associate(User::factory()->create());
            }
            if ($reaction->photo === null) {
                $reaction->photo()->associate(Photo::factory()->create());
            }
        });
    }
}
