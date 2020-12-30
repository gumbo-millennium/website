<?php

declare(strict_types=1);

namespace App\Models\Traits;

/**
 * Quickly creates paperclipped media using a table
 */
trait HasSimplePaperclippedMedia
{
    /**
     * Quickly creates a paperclip with given variants on the property.
     * The $values should be string => [int, int, bool].
     *
     * @param string $property
     * @param array $values
     * @return void
     */
    protected function createSimplePaperclip(
        string $property,
        array $values,
        string $disk = 'paperclip-public'
    ): void {
        // Build simple steps
        $variants = [];
        foreach ($values as $name => [$width, $height, $crop]) {
            $modifier = $crop ? '#' : '';
            $variants[$name] = sprintf('%dx%d%s', $width, $height, $modifier);
            $variants["$name-2x"] = sprintf('%dx%d%s', $width * 2, $height * 2, $modifier);
            $variants["$name-half"] = sprintf('%dx%d%s', $width / 2, $height / 2, $modifier);
        }

        // The actual attachment
        $this->hasAttachedFile($property, [
            'storage' => $disk,
            'variants' => $variants,
        ]);
    }
}
