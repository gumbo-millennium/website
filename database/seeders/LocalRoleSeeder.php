<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class LocalRoleSeeder extends Seeder
{
    private const ROLES = [
        'ic' => 'Introductie Commissie',
        'lhw' => 'Landhuisweekend Commissie',
        'prpg' => 'Promotie Projectgroep',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        foreach (self::ROLES as $name => $title) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ], [
                'title' => $title,
                'conscribo_id' => $faker->unique()->randomNumber(4),
            ]);
        }
    }
}
