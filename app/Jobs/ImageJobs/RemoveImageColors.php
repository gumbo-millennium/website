<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

class RemoveImageColors extends SvgJob
{
    private const TARGET_COLOR = 'currentColor';

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        // Get temp file
        $file = $this->getTempFile();
        $filePath = $file->getPathname();

        // Prep a document reader
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->strictErrorChecking = false;

        // Disable errors
        \libxml_use_internal_errors(true);

        // Try loading the XML (should work)
        $loadOk = $doc->load($filePath);

        // Enable errors
        \libxml_use_internal_errors(false);

        // Remove if conversion failed
        if (!$loadOk) {
            $this->updateAttribute(null);
            return;
        }

        // Remove doctype element (again)
        foreach ($doc->childNodes as $child) {
            if ($child->nodeType === \XML_DOCUMENT_TYPE_NODE) {
                $child->parentNode->removeChild($child);
            }
        }

        // Check all elements for fill and border
        foreach ($doc->getElementsByTagName('*') as $node) {
            \assert($node instanceof \DOMElement);

            // Replace fill with new color
            if ($node->hasAttribute('fill')) {
                $node->setAttribute('fill', self::TARGET_COLOR);
            }

            // Replace stroke with new color
            if ($node->hasAttribute('stroke')) {
                $node->setAttribute('stroke', self::TARGET_COLOR);
            }
        }

        // Write SVG back to the file
        if ($doc->save($filePath, \LIBXML_NOEMPTYTAG) !== false) {
            // Save attribute
            $this->updateAttribute($file);
        }
    }
}
