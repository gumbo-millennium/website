<?php

declare(strict_types=1);

namespace App\Console\Commands\Avg;

use App\Jobs\User\DeleteOldUserJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Laravel\Prompts\Progress;

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
        $users = User::query()
            ->withoutGlobalScopes()
            ->where('last_seen_at', '<', Date::today()->subyear())
            ->get();

        $this->line("Targetted <info>{$users->count()}</info> users");

        $progress = new Progress('Pruning users', $users);
        $progress->start();

        foreach ($users as $user) {
            Queue::push(new DeleteOldUserJob(
                user: $user,
            ));

            $progress->advance();
        }

        $progress->finish();
    }
}
