<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Middleware\CheckSignedUrl;

/**
 * Constructs paths to Glide images
 * @package App\Services
 */
final class GlideImageService
{
    /**
     * Returns path to image, takes Glide args or a string for a preset
     * @param null|string $image Image, can be null
     * @param string|array $args
     * @return null|string
     */
    public function url(?string $image, $args): ?string
    {
        if ($image === null) {
            return null;
        }

        // Convert to array
        if (\is_string($args) && !\blank($args)) {
            // Assign preset
            $args = ['p' => $args];
        }

        // Make empty array if not
        if (!\is_array($args) || empty($args)) {
            $args = [];
        }

        // Build a clean path
        $safePath = \trim(\base64_encode($image), '==');

        return CheckSignedUrl::signUrl(
            'glide-image',
            \array_merge($args, ['path' => $safePath])
        );
    }
}
