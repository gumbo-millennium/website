<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

use App\Helpers\Str;
use DOMDocument;
use SSNepenthe\ColorUtils\Exceptions\InvalidArgumentException as ColorException;
use function SSNepenthe\ColorUtils\is_bright;

class RemoveImageColors extends SvgJob
{
    private const TARGET_COLOR = 'currentColor';

    private const NO_COLOR = 'transparent';

    /**
     * Nodes that aren't expected to have child nodes.
     */
    private const SVG_END_NODES = [
        'circle',
        'ellipse',
        'path',
        'polygon',
        'polyline',
        'text',
        'textPath',
        'line',
    ];

    /**
     * Nodes which we can recurse into.
     */
    private const SVG_RECURSABLE = [
        'svg',
        'g',
    ];

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

        // Prep a document reader
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->strictErrorChecking = false;

        // Disable errors
        \libxml_use_internal_errors(true);

        // Try loading the XML (should work)
        $loadOk = $doc->load($filePath);

        // Enable errors
        \libxml_use_internal_errors(false);

        // Remove if conversion failed
        if (! $loadOk) {
            $this->updateAttribute(null);

            return;
        }

        // Remove doctype element (again)
        foreach ($doc->childNodes as $child) {
            if ($child->nodeType !== \XML_DOCUMENT_TYPE_NODE) {
                continue;
            }

            $child->parentNode->removeChild($child);
        }

        // Remove non-whitelisted nodes
        $whitelistNodes = \array_merge(self::SVG_END_NODES, self::SVG_RECURSABLE);
        foreach ($doc->documentElement->getElementsByTagName('*') as $node) {
            \assert($node instanceof \DOMElement);

            // Get clean name
            $nodeName = Str::lower($node->nodeName);

            // Check whitelist
            if (\in_array($nodeName, $whitelistNodes, true)) {
                continue;
            }

            // Check if parent still exists
            if (! $node->parentNode) {
                continue;
            }

            // Remove node
            $node->parentNode->removeChild($node);
        }

        // Check recursively for colors in elements, in a safe method
        $this->recurseColorCheck($doc->documentElement);

        // Remove

        // Write SVG back to the file
        if ($doc->save($filePath, \LIBXML_NOEMPTYTAG) === false) {
            return;
        }

        // Save attribute
        $this->updateAttribute($file);
    }

    /**
     * Recurses into nodes.
     *
     * @param DOMElement $root The root element we're inspecting
     */
    private function recurseColorCheck(\DOMElement $root): void
    {
        foreach ($root->childNodes as $node) {
            // Skip non-element nodes
            if (! $node instanceof \DOMElement) {
                continue;
            }

            // Check node
            $nodeName = Str::lower($node->nodeName);

            // Check type
            $hasChildren = $node->hasChildNodes();
            $isEndNode = \in_array($nodeName, self::SVG_END_NODES, true);

            // We're at the end of the tree, and no color is set, so assign a new one
            if (($isEndNode || ! $hasChildren) && $this->hasNoConfiguredFill($node)) {
                $node->setAttribute('fill', self::TARGET_COLOR);
            }

            // Replace color
            $this->updateColorAttribute($node, 'fill');
            $this->updateColorAttribute($node, 'stroke');

            // Skip if no child nodes
            if (! $hasChildren) {
                continue;
            }

            // Only recurse if allowed by standard
            if (! \in_array($nodeName, self::SVG_RECURSABLE, true)) {
                continue;
            }

            // Descend into groups or svg images
            $this->recurseColorCheck($node);
        }
    }

    /**
     * Returns the color this element should recieve (currentColor or transparent).
     */
    private function determineColor(string $color): string
    {
        // No color is set, return target color
        if (empty($color)) {
            return self::TARGET_COLOR;
        }

        try {
            // Check if bright
            return is_bright($color, 230) ? self::NO_COLOR : self::TARGET_COLOR;
        } catch (ColorException $exception) {
            // Handle exceptions by returning the target color (for gradients and such)
            return self::TARGET_COLOR;
        }

        // Return transparent if bright
    }

    /**
     * Determines attribute value for $attributeName.
     *
     * @param DOMElement $element
     */
    private function updateColorAttribute(\DOMElement $element, string $attributeName): void
    {
        // Get fill attribute
        $currentColor = $element->getAttribute($attributeName);
        if (empty($currentColor)) {
            return;
        }

        // Get new target color
        $element->setAttribute($attributeName, $this->determineColor($currentColor));
    }

    /**
     * Returns true if this element has no fill.
     *
     * @param DOMElement $element
     */
    private function hasNoConfiguredFill(\DOMElement $element): bool
    {
        return empty($element->getAttribute('fill'))
            && empty($element->getAttribute('stroke'));
    }
}
