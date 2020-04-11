<?php

declare(strict_types=1);

namespace App\Jobs\ImageJobs;

use App\Models\Sponsor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Jobs to convert SVGs
 */
abstract class SvgJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected const COMPRESSION_TYPES = [
        'br' => ['brotli', '-k', '-Z'],
        'gz' => ['gzip', '-k', '-9']
    ];

    protected const DISK = Sponsor::LOGO_DISK;

    /**
     * Sponsor to update
     * @var Sponsor
     */
    protected Sponsor $model;

    /**
     * Attribute on the sponsor that needs updating
     * @var string
     */
    protected string $attribute;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct(Sponsor $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    /**
     * Returns false if current model file does not exist.
     * @return bool
     */
    protected function exists(): bool
    {
        if (!$this->getPath()) {
            return false;
        }

        return Storage::disk(Sponsor::LOGO_DISK)
            ->exists($this->getPath());
    }

    /**
     * Returns path to the svg
     * @return string
     */
    protected function getPath(): ?string
    {
        return $this->model->{$this->attribute};
    }

    /**
     * Converts the file on the model to an actual file
     * @return null|File
     * @throws InvalidArgumentException
     */
    private function createTempFile(): ?File
    {
        // Fail if missing
        if (!$this->exists()) {
            echo "NOT FOUND\n";
            return null;
        }

        // Get handle on source
        $sourceHandle = Storage::disk(Sponsor::LOGO_DISK)->readStream($this->getPath());
        if (!$sourceHandle || !\is_resource($sourceHandle)) {
            echo "HANDLE FAILED\n";
            return null;
        }

        // Get handle on target
        $targetFile = \tempnam(\sys_get_temp_dir(), 'svg');
        $targetHandle = fopen($targetFile, 'w');
        if (!$targetHandle || !\is_resource($targetHandle)) {
            echo "HANDLE TARGET FAILED\n";
            fclose($sourceHandle);
            return null;
        }

        // Copy over file
        $copyOk = \stream_copy_to_stream($sourceHandle, $targetHandle);

        // Close handles
        fclose($sourceHandle);
        fclose($targetHandle);

        // Return null on failure
        if ($copyOk === false) {
            @unlink($targetFile);
            echo "COPY FAILED\n";
            return null;
        }

        // Return file
        return new File($targetFile);
    }

    /**
     * Returns file or fails the job
     * @return File
     */
    protected function getTempFile(): File
    {
        $file = $this->createTempFile();
        if ($file) {
            return $file;
        }

        // BUild error
        $exception = new \RuntimeException('Cannot create file');

        // Fail task
        $this->fail($exception);

        // Thorw it too
        throw $exception;
    }

    /**
     * Replaces file in the attribute
     * @param null|File $file
     * @return void
     * @throws InvalidArgumentException
     */
    protected function updateAttribute(?File $file): ?string
    {
        // Base paths
        $newPath = null;
        $oldPath = $this->model->{$this->attribute};

        // Write stream if applicable
        if ($file !== null) {
            // Get path
            $path = $file->getPathname();

            // Create filename
            $fileTarget = sprintf(
                '%s.%s.%s.svg',
                $this->model->slug,
                substr(\sha1("{$this->attribute}-{$this->model->id}"), 0, 4),
                substr(\sha1_file($path), 0, 16)
            );

            // Store original version
            $newPath = Storage::disk(Sponsor::LOGO_DISK)
                ->putFileAs(Sponsor::LOGO_PATH, $file, $fileTarget);
        }

        // Delete old path
        if ($oldPath !== null && $oldPath !== $newPath) {
            Storage::disk(Sponsor::LOGO_DISK)->delete($oldPath);
        }

        // Update model
        $this->model->{$this->attribute} = $newPath;
        $this->model->withoutEvents(fn () => $this->model->save([$this->attribute]));

        // Return path
        return $newPath;
    }
}
