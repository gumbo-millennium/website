<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

use Symfony\Component\Process\Process;

class CompressSvg extends SvgJob
{
    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        // Get temp file
        $file = $this->getTempFile();
        $filePath = $file->getPathname();

        // Run it through SVGO
        $process = new Process(['svgo', $filePath]);
        $process->run();

        // Log if SVGO failed
        if (!$process->isSuccessful()) {
            \logger()->warning('Cannot process svg');
        }

        // Save attribute
        $this->updateAttribute($file);
    }
}
