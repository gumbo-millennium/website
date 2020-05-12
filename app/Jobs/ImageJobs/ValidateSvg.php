<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

use enshrined\svgSanitize\Sanitizer;

class ValidateSvg extends SvgJob
{
    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        // Skip if not found
        if (!$this->exists()) {
            $this->fail();
            return;
        }

        // Get temp file
        $file = $this->getTempFile();
        $filePath = $file->getPathname();

        // Start SVG validator
        $sanitizer = new Sanitizer();
        $sanitizer->removeRemoteReferences(true);

        // Clean contents
        $cleanContents = $sanitizer->sanitize(\file_get_contents($filePath));

        // Return first error if cleanContents is false
        if ($cleanContents !== false) {
            // Update contents
            \file_put_contents($filePath, $cleanContents);

            // Send to attribute
            $this->updateAttribute($file);
            return;
        }

        // Build issue string
        $issues = $sanitizer->getXmlIssues();

        // Count issues
        if (count($issues) === 0) {
            $this->fail(new \InvalidArgumentException('SVG XML cannot be parsed'));
            return;
        }

        // Get first issue and form sentence
        $firstIssue = head($issues);
        $message = sprintf('%s on line %d', $firstIssue['message'], $firstIssue['line']);

        // Return if there's just one issue
        if (count($issues) === 1) {
            $this->fail(new \InvalidArgumentException("Error: {$message}"));
            return;
        };

        // Returns the first issue and hints about the rest
        $restCount = count($issues) - 1;
        $this->fail(new \InvalidArgumentException("Errors: {$message} and {$restCount} more"));
    }
}
