<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\FileBundle;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;

class RecomputeFileBundleSize
{
    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
        //
    }

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

        // Recompute size
        $size = $model->getMedia()->sum('size');
        $model->total_size = $size;
        $model->save();
    }
}
