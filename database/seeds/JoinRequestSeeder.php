<?php

use Illuminate\Database\Seeder;

class JoinRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\JoinRequest::class)->create();
    }
}
