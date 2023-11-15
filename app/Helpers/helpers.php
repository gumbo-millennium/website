<?php

declare(strict_types=1);

use App\Fluent\Image;
use App\Helpers\Str;
use Brick\Money\Context;
use Brick\Money\Money;
use Illuminate\Support\Collection;

if (! function_exists('mix_file')) {
    function mix_file(string $file): ?string
    {
        // Ask Laravel Mix for the file
        $url = (string) mix($file);
        if (empty($url)) {
            return null;
        }

        // Strip the default URL
        $path = Str::after($url, (string) app('config')->get('app.mix_url'));
        if (empty($path)) {
            return null;
        }

        // Edge case for hot builds
        if (Str::startsWith($path, ['https://', 'http://'])) {
            $path = parse_url($path, PHP_URL_PATH);
        }

        // Remove queries
        $path = Str::before($path, '?');

        // Convert to public path
        $fullPath = public_path($path);
        if (! file_exists($fullPath)) {
            return null;
        }

        // Get contents
        return file_get_contents($fullPath, false, null, 0, 1024 * 1024);
    }
}

if (! function_exists('image_asset')) {
    /**
     * Makes an image fluent from the given file.
     */
    function image_asset(?string $file): Image
    {
        return Image::make($file);
    }
}

if (! function_exists('path_join')) {
    /**
     * Joins a given set of paths, skipping `null` values.
     */
    function path_join(?string ...$paths): string
    {
        $startsWithSlash = ! empty($paths[0]) && Str::startsWith($paths[0], '/');

        $joinedPaths = Collection::make($paths)
            ->filter()
            ->map(fn ($segment) => trim($segment, '/'))
            ->implode('/');

        return $startsWithSlash ? "/{$joinedPaths}" : $joinedPaths;
    }
}

if (! function_exists('money_value')) {
    /**
     * Converts an amount to a Money object, unless nil.
     * @param null|Context $context the money context to perform this operation in
     */
    function money_value(null|int|string|array|object $value, ?Context $context = null): ?Money
    {
        if (null === $value) {
            return null;
        }

        if (is_int($value)) {
            return Money::ofMinor($value, 'EUR', $context);
        }

        if (is_string($value)) {
            return Money::of($value, 'EUR', $context);
        }

        if (is_array($value)) {
            $value = (object) $value;
        }

        if (property_exists($value, 'value') && property_exists($value, 'currency')) {
            return Money::of($value->value, $value->currency, $context);
        }

        if (property_exists($value, 'amount')) {
            return Money::of($value->amount, 'EUR', $context);
        }

        throw new InvalidArgumentException('Invalid money value');
    }
}
