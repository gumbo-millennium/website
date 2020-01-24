<?php

use App\Jobs\CreateVersionedPagesJob;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

// Update command
Artisan::command('page:update-git-pages', fn() => app()->call([app()->make(CreateVersionedPagesJob::class), 'handle']));
