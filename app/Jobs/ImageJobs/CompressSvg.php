<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CompressSvg extends SvgJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get temp file
        $file = $this->getTempFile();
        $filePath = $file->getPathname();

        $configPath = resource_path('js/sponsors.svgo.config.js');

        // Run it through SVGO
        $process = new Process([
            'svgo',
            "--config={$configPath}",
            $filePath,
        ]);
        $process->run();

        // Log if SVGO failed
        if (! $process->isSuccessful()) {
            Log::warning('Cannot process svg');
        }

        // Save attribute
        $this->updateAttribute($file);
    }
}
