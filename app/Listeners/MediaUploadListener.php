<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\IndexFileContents;
use App\Models\FileBundle;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;

class MediaUploadListener
{
    /**
     * Handle the event.
     * @param  object  $event
     * @return void
     */
    public function handle(MediaHasBeenAdded $event)
    {
        $media = $event->media;
        $model = $media->model;

        // Skip if wrong type
        if (!$model instanceof FileBundle) {
            return;
        }

        // Dispatch file indexes
        IndexFileContents::dispatch($media);

        // Update file size
        $this->updateFileSize($model);
    }

    /**
     * Recomputes total bundle size
     * @param FileBundle $bundle
     * @return void
     */
    private function updateFileSize(FileBundle $bundle)
    {
        // Recompute size
        $size = $bundle->getMedia()->sum('size');
        $bundle->total_size = $size;
        $bundle->save();
    }
}
