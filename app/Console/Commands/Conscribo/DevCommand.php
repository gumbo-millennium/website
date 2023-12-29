<?php

declare(strict_types=1);

namespace App\Console\Commands\Conscribo;

use App\Services\Conscribo\Contracts\Client;
use App\Services\Conscribo\Data\EntityGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class DevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conscribo:dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper command to configure Conscribo configuration.';

    /**
     * Hide this command in production.
     */
    public function isHidden(): bool
    {
        return App::isProduction();
    }

    /**
     * Execute the console command.
     */
    public function handle(Client $client)
    {
        $this->line(sprintf('<fg=yellow>=== %s ===</>', str_repeat('=', 24)));
        $this->line(sprintf('<fg=yellow>=== %s ===</>', str_pad('Conscribo dev', 24, ' ', STR_PAD_BOTH)));
        $this->line(sprintf('<fg=yellow>=== %s ===</>', str_repeat('=', 24)));

        $this->line('Fetching groups...');
        $groups = $client->listGroups();

        $this->info('Available groups');
        $this->line('You can use the IDs below to configure <fg=cyan>gumbo.conscribo.member_groups</>.');
        $this->table(
            ['ID', 'Name', 'Member count'],
            $groups->sortBy('id')->map(fn (EntityGroup $group) => [
                $group->id,
                $group->name,
                $group->members->count(),
            ]),
        );
    }
}
