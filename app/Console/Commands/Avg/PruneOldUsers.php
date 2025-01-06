<?php

declare(strict_types=1);

namespace App\Console\Commands\Avg;

use App\Jobs\User\DeleteUserJob;
use App\Jobs\User\NotifyDeletedUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Date;

class PruneOldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:avg:prune-old-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete accounts that have not logged in for more than a year.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = User::query()
            ->where('last_seen_at', '<', Date::today()->subyear())
            ->cursor();

        foreach ($accounts as $account) {
            Bus::chain([
                new DeleteUserJob($account),
                new NotifyDeletedUserJob($account),
            ])->dispatch();
        }
    }
}
