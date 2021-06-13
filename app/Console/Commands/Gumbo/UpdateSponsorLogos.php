<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Helpers\Str;
use App\Models\Sponsor;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSponsorLogos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:update-sponsor-logos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forces an update of sponsor logos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Switch to realtime job handling
        $queueDefault = config('queue.default');
        config(['queue.default' => 'sync']);

        // Handle each sponsor
        foreach (Sponsor::cursor() as $sponsor) {
            // Get assets
            $color = $sponsor->logo_color;
            $gray = $sponsor->logo_gray;

            // Update values, if set
            $color and $sponsor->logo_color = Str::uuid();
            $gray and $sponsor->logo_gray = Str::uuid();

            // Sync model
            $sponsor->syncChanges();

            // Re-assign values
            $sponsor->logo_color = $color;
            $sponsor->logo_gray = $gray;

            // Save, triggering observer
            $sponsor->save();

            // Log
            $this->line("Updated sponsor {$sponsor->name}", null, OutputInterface::VERBOSITY_VERBOSE);
        }

        // Reset config
        config(['queue.default' => $queueDefault]);

        // Done
        $this->info('Sponsor logos updated');
    }
}
