<?php

namespace App\Console\Commands;

use App\Jobs\DeleteUserJob;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Deletes all users older than 90 days
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class GdprFlushUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdpr:flush-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove users deleted > 90 days ago';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::onlyTrashed()
            ->where('deleted_at', '<', today()->subDays(90))
            ->get();

        // Fire a delete command for each outdated user
        foreach ($users as $user) {
            dispatch(new DeleteUserJob($user));
        }
    }
}
