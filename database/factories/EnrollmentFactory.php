<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $activity = Activity::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();

        return [
            'user_id' => $user->id,
            'user_type' => $user->hasRole('member') ? 'member' : 'guest',
            'activity_id' => $activity->id,
            'price' => $this->faker->boolean ? $activity->price : $activity->price - $activity->member_discount,
        ];
    }
}
