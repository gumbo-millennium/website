<?php

declare(strict_types=1);

use App\Helpers\Str;
use App\Models\EmailList;
use App\Services\Mail\GoogleMailList;
use Faker\Generator as Faker;

$factory->define(EmailList::class, static function (Faker $faker) {
    // Prep a set of members
    $emails = [];
    for ($i = 0; $i < $faker->numberBetween(1, 20); $i++) {
        $emails[] = [
            'email' => $faker->safeEmail,
            'role' => $faker->boolean(95) ? GoogleMailList::ROLE_NAME_NORMAL : GoogleMailList::ROLE_NAME_ADMIN,
        ];
    }

    // Prep some aliases
    $aliases = [];
    for ($i = 0; $i < $faker->numberBetween(0, 6); $i++) {
        $aliases[] = $faker->safeEmail;
    }

    // Done
    return [
        'name' => $faker->words(3, true),
        'email' => $faker->safeEmail,
        'service_id' => Str::random(16),
        'aliases' => $aliases,
        'members' => $emails,
    ];
});
