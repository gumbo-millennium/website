<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FileDownload;

/**
 * Removes IP addresses from downloads more than 90 days ago
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class GdprCleanDownloadIps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdpr:clean-download-ips
                            {--m|more-recent : Remove IPs older than 30 days}
                            {--all : Removes ALL ips}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes IPs associated with downloads older than 90 days';

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
        $this->line(sprintf('Removed <info>%d</> IP address(es).', $ipCount));
    }
}
