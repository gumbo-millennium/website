<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Jobs\Mail\ConstructGoogleActionList;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates all users from Conscribo
 */
class UpdateEmailLists extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'gumbo:update-lists';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Updates the mail lists on Google with data from the Conscribo API';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // Ensure verbosity is set
        $verbosity = $this->output->getVerbosity();
        $this->setVerbosity($verbosity);

        // Check verbosity
        if ($this->verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
            // Change to write to stdout
            Config::set('logging.channels.stderr.with', ['stream' => 'php://stdout']);

            // Verbosity
            $level = 'notice';
            if ($this->verbosity >= OutputInterface::VERBOSITY_DEBUG) {
                $level = 'debug';
            } elseif ($this->verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $level = 'info';
            }

            // Update level
            Config::set('logging.channels.stderr.level', $level);

            // Switch to driver
            Log::setDefaultDriver('stderr');
        }

        // Run jobs in sync
        Config::set('queue.default', 'sync');

        // Print Start
        $this->info('Starting update job...');
        $start = Date::now();

        // Fire job
        ConstructGoogleActionList::dispatch();

        // Print end
        $time = Date::now()->locale('en')->diffForHumans($start, CarbonInterface::DIFF_ABSOLUTE);
        $this->info("Updated all mail groups in {$time}.");
    }
}
