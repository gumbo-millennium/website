<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Shop\Order;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Order::class, static function (Faker $faker) {
    return [
        'paid_at' => $faker->optional()->dateTimeBetween('-1 year', '5 seconds ago'),
        'user_id' => User::inRandomOrder()->first()->id,
        'price' => $faker->numberBetween(150, 15000),
    ];
});
