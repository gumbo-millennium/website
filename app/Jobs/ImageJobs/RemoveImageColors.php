<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

use SSNepenthe\ColorUtils\Exceptions\InvalidArgumentException;

use function SSNepenthe\ColorUtils\is_bright;

class RemoveImageColors extends SvgJob
{
    private const TARGET_COLOR = 'currentColor';
    private const NO_COLOR = 'transparent';

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

        $checkElements = ['fill', 'stroke'];

        // Check all elements for fill and border
        foreach ($doc->getElementsByTagName('*') as $node) {
            \assert($node instanceof \DOMElement);

            // Replace colors
            foreach ($checkElements as $type) {
                if (!$node->hasAttribute($type)) {
                    continue;
                }

                try {
                    $isLight = is_bright($node->getAttribute($type), 210);
                    $node->setAttribute($type, $isLight ? self::NO_COLOR : self::TARGET_COLOR);
                } catch (InvalidArgumentException $exception) {
                    // Noop
                    continue;
                }
            }
        }

        // Write SVG back to the file
        if ($doc->save($filePath, \LIBXML_NOEMPTYTAG) !== false) {
            // Save attribute
            $this->updateAttribute($file);
        }
    }
}
