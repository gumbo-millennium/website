<?php

declare(strict_types=1);

namespace App\Console\Commands\GoogleWallet;

use App\Jobs\GoogleWallet\CreateEventClassJob;
use App\Models\Activity;
use Illuminate\Console\Command;

class CreateEventClassCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-wallet:create {activity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the Google Wallet Class for the given activity';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $activity = $this->argument('activity');
        $activity = Activity::query(fn ($query) => $query->orWhere([
            [
                'id',
                $activity,
            ],
            [
                'slug',
                $activity,
            ],
        ]))->first();

        if (! $activity) {
            $this->error('Activity not found');

            return 1;
        }

        $this->info('Creating event class for activity ' . $activity->id);

        CreateEventClassJob::dispatchSync($activity);

        return 0;
    }
}
