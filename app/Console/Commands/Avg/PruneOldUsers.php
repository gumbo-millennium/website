<?php

declare(strict_types=1);

namespace App\Console\Commands\Avg;

use App\Jobs\User\DeleteOldUserJob;
use App\Models\User;
use Illuminate\Console\Command;
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
    public function handle(): void
    {
        $accountQuery = User::query()
            ->where('last_seen_at', '<', Date::today()->subyear());

        $this->line("Targetted <info>{$accountQuery->count()}</info> users");

        foreach ($accountQuery->lazy() as $account) {
            DeleteOldUserJob::dispatch($account);
        }
    }
}
