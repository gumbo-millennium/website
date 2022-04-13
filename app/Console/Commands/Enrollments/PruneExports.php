<?php

namespace App\Console\Commands\Enrollments;

use App\Services\ActivityExportService;
use Illuminate\Console\Command;

class PruneExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:prune-exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old exports';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ActivityExportService $service)
    {
        $service->pruneExports();

        $this->info('Deleted expired exports');

        return Command::SUCCESS;
    }
}
