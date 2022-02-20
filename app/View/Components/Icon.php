<?php

declare(strict_types=1);

namespace App\View\Components;

use DOMDocument;
use DOMElement;
use Generator;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use RuntimeException;

class Icon extends Component
{
    /**
     * Paths that are like disk://path.
     */
    protected const STORAGE_PATH_REGEX = '/^(?<disk>[a-z][a-z0-9_-]+):\/\/(?<path>.+)$/';

    /**
     * Max size in megabytes.
     */
    protected const MAX_FILE_SIZE = 2 * 1024 * 1024;

    protected static ?array $cachedPaths = [];

    protected ?string $icon;

    /**
     * Returns the safe paths to allow icons to be loaded from, if they're
     * not loaded from disk.
     *
     * @return array<string>
     */
    protected static function getSafePaths(): array
    {
        // Only allow certain root paths
        return static::$cachedPaths ??= [
            storage_path('app/font-awesome/'),
            resource_path('assets/'),
            resource_path('svg/'),
            resource_path('icons/'),
            public_path('assets/'),
            public_path('svg/'),
            public_path('icons/'),
        ];
    }

    public function __construct(?string $icon = null)
    {
        $this->icon = $icon;
    }

    /**
     * Get the view / view contents that represent the component.
     */
    public function render()
    {
        if ($this->icon == null) {
            return "<!-- NULL value for icon -->";
        }

        $contents = $this->getCleanIconContents($this->icon);

        // If not found and in production, fail quietly.
        if ($contents === null && (App::isProduction() || App::runningUnitTests())) {
            return sprintf('<!-- Missing icon [%s] -->', e($this->icon));
        }

        // If not found and not in production, throw an error
        if ($contents === null) {
            throw new RuntimeException(sprintf(
                'Failed to find suitable path for icon [%s], looked in ([%s])',
                $this->icon,
                implode('], [', [...$this->determineIconPaths($this->icon)]),
            ));
        }

        return fn (array $data) => str_replace(
            '<svg',
            trim("<svg {$data['attributes']->merge(['class' => 'icon'])}"),
            $contents,
        );
    }

    protected function getCleanIconContents(string $path): ?string
    {
        // Check cached file
        $cacheKey = sprintf('icons.cached.%s', md5($path));

        // Check cache
        if ($cachedIcon = Cache::get($cacheKey)) {
            return $cachedIcon;
        }

        // Get icon and fail if missing
        $icon = $this->getIconContents($path);
        if (! $icon) {
            return null;
        }

        // Clean up XML
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($icon);

        $rootNode = $doc->documentElement;
        $nodes = $rootNode->childNodes;

        // Iterate in reverse order, since we're removing nodes
        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $node = $nodes->item($i);

            // Remove non-elements
            if (! $node instanceof DOMElement) {
                $rootNode->removeChild($node);

                continue;
            }

            assert($node instanceof DOMElement);

            // If the stroke is set, only color that
            if ($stroke = $node->getAttribute('stroke')) {
                if (! Str::startsWith($stroke, 'url(')) {
                    $node->setAttribute('stroke', 'currentColor');
                }

                continue;
            }

            // Check if the fill isn't a URL or is unset
            if ($fill = $node->getAttribute('fill')) {
                if (Str::startsWith($fill, 'url(')) {
                    continue;
                }
            }

            // fill is not a gradient URL or is unset, set it to the current text color
            $node->setAttribute('fill', 'currentColor');
        }

        // Add accessibility nodes to DOMDocument
        $rootNode->setAttribute('role', 'none');
        $rootNode->setAttribute('aria-hidden', 'true');

        // Convert to XML
        $icon = $doc->saveXML($rootNode);

        // Cache
        $cacheUntil = App::isProduction() ? Date::now()->addHour() : Date::now()->addSecond();
        Cache::put($cacheKey, $icon, $cacheUntil);

        // Done
        return $icon;
    }

    /**
     * Returns the contents of the file.
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    protected function getIconContents(string $path): ?string
    {
        // Safety check, all files need to end in SVG.
        $path = Str::finish($path, '.svg');

        foreach ($this->determineIconPaths($path) as $pathOption) {
            // Allow for disk://<path> patterns
            if (preg_match(static::STORAGE_PATH_REGEX, $pathOption, $matches, PREG_UNMATCHED_AS_NULL)) {
                // Skip if the files are not there
                if (! Storage::disk($matches['disk'])->exists($matches['path'])) {
                    continue;
                }

                // Return the disk
                return Storage::disk($matches['disk'])
                    ->get($matches['path']);
            }

            // Files that don't exist are, of course, skipped
            if (! file_exists($pathOption)) {
                continue;
            }

            // 🤩
            return file_get_contents($pathOption);
        }

        return null;
    }

    /**
     * Ensure the path is safe.
     *
     * @throws BindingResolutionException
     */
    protected function isSafePath(string $path): bool
    {
        // If the path is missing, assume it's insecure
        $actualPath = realpath($path);
        if (! $actualPath) {
            return false;
        }

        // Check the paths agains the safe paths
        if (! Str::startsWith($actualPath, static::getSafePaths())) {
            return false;
        }

        // Files that are not files are unsafe too
        if (! is_file($path)) {
            return false;
        }

        // Files over a certain size are unsafe too, just to put up a barrier
        if (filesize($path) > static::MAX_FILE_SIZE) {
            return false;
        }

        return true;
    }

    /**
     * Returns all paths to check, should be relative to the resource folder, or in the
     * storage disks.
     *
     * @return Generator<string>
     */
    protected function determineIconPaths(string $path): Generator
    {
        $options = [
            $path,
            "assets/{$path}",
            "svg/{$path}",
            "icons/{$path}",
        ];

        yield storage_path("app/font-awesome/{$path}");

        foreach ($options as $option) {
            yield resource_path($option);

            yield public_path($option);
        }

        yield "public://icons/{$path}";

        yield "public://svg/{$path}";
    }
}
