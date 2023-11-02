<?php

declare(strict_types=1);

namespace App\Jobs\Images;

use App\Fluent\CachedImage;
use App\Services\ImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File as HttpFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Image;
use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image as ImageObject;
use InvalidArgumentException;
use RuntimeException;

class CreateSizedImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private Model $model,
        private string $attribute,
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImageService $service)
    {
        // Firstly, load the data
        $modelValue = $this->model->{$this->attribute};
        if ($modelValue instanceof CachedImage) {
            $modelValue = $modelValue->getValue();
        }

        // Skip if empty or invalid
        if (empty($modelValue)) {
            return;
        }

        // Check path
        if (! is_string($modelValue) || ! $service->isValidSourcePath($modelValue)) {
            Log::error('Invalid value on model {model}, attribute {attribute}.', [
                'model' => get_class($this->model),
                'attribute' => $this->attribute,
                'value' => $modelValue,
            ]);

            $this->fail(new RuntimeException('Invalid image path'));
        }

        $sourceFile = tempnam(sys_get_temp_dir(), 'image');
        $resizedFiles = [];

        try {
            // Load image
            $image = $this->loadImageFile($service, $sourceFile, $modelValue);

            Log::debug('Loaded image {image}', [
                'image' => $modelValue,
            ]);

            // Fix orientation
            $image->orientate();

            // Snapshot
            $image->backup();

            // Make resized images
            $resizedFiles = $this->createResizedImages($service, $image);

            // Clear memory
            $image->destroy();

            // Prep upload
            $storageDiskName = $service->getStorageDiskName();
            Log::debug('Created {files} to write away to {disk}', [
                'files' => implode(', ', $resizedFiles),
                'disk' => $storageDiskName,
            ]);

            // Upload resized images
            $storageDisk = Storage::disk($storageDiskName);
            foreach ($resizedFiles as $size => $path) {
                $targetFile = $service->getStoragePathForImage($this->model, $modelValue, $size);

                $storageDisk->putFileAs(
                    dirname($targetFile),
                    new HttpFile($path),
                    basename($targetFile),
                );

                Log::debug('Wrote file {file} as {size}.webp', [
                    'file' => $path,
                    'size' => $size,
                ]);
            }
        } finally {
            file_exists($sourceFile) && unlink($sourceFile);
            foreach ($resizedFiles as $resizedFile) {
                file_exists($resizedFile) && unlink($resizedFile);
            }
        }
    }

    /**
     * Load the image to the tempfile using a stream.
     */
    private function loadImageFile(ImageService $service, string $tempFile, string $sourcePath): ImageObject
    {
        $readStream = $writeStream = null;

        try {
            $readStream = Storage::disk($service->getSourceDiskName())->readStream($sourcePath);
            $writeStream = fopen($tempFile, 'w');

            if (! $readStream || ! $writeStream) {
                throw new RuntimeException('Could not open streams.');
            }

            stream_copy_to_stream($readStream, $writeStream);
        } finally {
            is_resource($readStream) && @fclose($readStream);
            is_resource($writeStream) && @fclose($writeStream);
        }

        return Image::make($tempFile);
    }

    /**
     * Creates all resized images, using an in-memory pointer to the create files to avoid
     * having to clean up the files in case of an exception.
     * @throws InvalidArgumentException
     * @throws NotWritableException
     * @throws NotSupportedException
     */
    private function createResizedImages(ImageService $service, ImageObject $image): array
    {
        $createdFiles = [];

        try {
            foreach ($service->getImageSizes() as $sizeName) {
                // Reset to original
                $image->reset();

                // Apply mutations
                $specs = $service->getImageSize($sizeName);
                $image->resize($specs['width'], $specs['height'], function ($constraint) use ($specs) {
                    if (! $specs['crop']) {
                        $constraint->aspectRatio();
                    }
                });

                // Save to temporary file
                $createdFiles[$sizeName] = $destFile = tempnam(sys_get_temp_dir(), 'image');
                $image->save($destFile, null, 'webp');
            }

            return $createdFiles;
        } finally {
            foreach ($createdFiles as $file) {
                file_exists($file) && unlink($file);
            }
        }
    }
}
