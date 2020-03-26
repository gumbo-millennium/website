<?php

declare(strict_types=1);

use App\Models\BotQuote;
use Illuminate\Database\Seeder;

class BotQuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        factory(BotQuote::class, 25)->create();
    }
}
