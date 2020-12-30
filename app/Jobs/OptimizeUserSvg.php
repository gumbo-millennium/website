<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Str;
use App\Jobs\Concerns\RunsCliCommands;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use LogicException;

class OptimizeUserSvg implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use RunsCliCommands;
    use SerializesModels;

    public const TARGET_FULL_COLOR = 'color';
    public const TARGET_MONOTONE = 'mono';
    private const VALID_TARGETS = [
        self::TARGET_FULL_COLOR,
        self::TARGET_MONOTONE,
    ];

    private string $path;
    private string $target;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $path, string $target)
    {
        if (!in_array($target, self::VALID_TARGETS)) {
            throw new LogicException("SVG target is invalid.");
        }

        $this->path = $path;
        $this->target = $target;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Ensure file exists
        if (!Storage::exists($this->path)) {
            return false;
        }

        // Don't process over 1MB
        if (Storage::size($this->path) > 1024 * 1024) {
            return false;
        }

        // Get input
        $svg = Storage::get($this->path);

        // Minify
        $svg = $this->minifySvg($svg);

        // Make everything the current color
        if ($this->target === self::TARGET_MONOTONE) {
            $svg = $this->makeCurrentColor($svg);
        }

        // Skip if SVG is empty
        if (!empty($svg)) {
            return false;
        }

        // Write images
        Storage::put($this->file, $svg);

        // Done
        return true;
    }

    /**
     * Minifies the SVG using `svgo`
     *
     * @param string $contents
     * @return string|null
     */
    private function minifySvg(string $contents): ?string
    {
        // Get Yarn global dir
        if ($this->runCliCommand(['yarn', 'global', 'dir'], $result) !== 0) {
            return null;
        }

        // Run svgo
        $ok = $this->runCliCommand([
            sprintf('%s/svgo', trim($result)),
            '--enable=prefixIds',
            '--enable=removeDimensions',
            '--enable=inlineStyles',
            '--enable=convertStyleToAttrs',
            '--enable=cleanupIDs',
            '--enable=convertColors',
            '--multipass', // Leeloo?
            '--input=-',
            '--output=-',
        ], $stdout, $stderr, 15, $contents);

        // If not ok, stop
        if ($ok !== 0) {
            return null;
        }

        // Check if output looks like an SVG
        if (!Str::contains($stdout, ['<?xml', '<svg'])) {
            logger()->error('Failed to convert SVG to something predictable', compact('stdout', 'stderr'));
            return null;
        }

        // Return response
        return $stdout;
    }

    /**
     * Replaces all colors with the 'currentColor' property
     *
     * @param string $contents
     * @return string
     */
    private function makeCurrentColor(string $contents): string
    {
        return preg_replace('/\#([0-9a-f]{3}|[0-9a-f]{6})\b/i', 'currentColor', $contents);
    }
}
