<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\FileDownload;
use Illuminate\Console\Command;

/**
 * Deletes data we're not supposed to keep for extended periods of time.
 */
class AvgFlush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avg:flush
                            {--m|more-recent : Remove more recent data (-30 days)}
                            {--all : Removes all non-critical data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes non-critical data older than 90 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = FileDownload::query()
            ->whereNotNull('ip');

        if ($this->option('more-recent')) {
            $query = $query->where('created_at', '<', today()->subDays(30));
        } elseif (! $this->option('all')) {
            $query = $query->where('created_at', '<', today()->subDays(90));
        }

        // Count results
        $count = $query->count();
        $result = $query->update(['ip' => null]);

        // Report result
        $this->line(sprintf('Cleansed <info>%d</> download logs.', $result ? $count : 0));
    }
}
