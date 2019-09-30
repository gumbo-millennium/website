<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FileDownload;

/**
 * Deletes data we're not supposed to keep for extended periods of time
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
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
     *
     * @return mixed
     */
    public function handle()
    {
        $query = FileDownload::query()
            ->whereNotNull('ip');

        if ($this->option('more-recent')) {
            $query = $query->where('downloaded_at', '<', today()->subDays(30));
        } elseif (!$this->option('all')) {
            $query = $query->where('downloaded_at', '<', today()->subDays(90));
        }

        // Run query
        $ipCount = $query->update(['ip' => null])->count();

        // Report result
        $this->line(sprintf('Cleansed <info>%d</> download logs.', $ipCount));
    }
}
