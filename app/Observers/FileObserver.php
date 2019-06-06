<?php

namespace App\Observers;

use App\Models\File;
use App\Jobs\FileMetaJob;
use App\Jobs\FileThumbnailJob;

class FileObserver
{
    /**
     * Handle the file "created" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function created(File $file)
    {
        // Queue file meta job, since there's a new file available.
        // The jobs fetch the file meta and generate a thumbnail.
        FileMetaJob::withChain([
            new FileThumbnailJob($file)
        ])->dispatch($file);
    }

    /**
     * Handle the file "updated" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function updated(File $file)
    {
        // no-op
    }

    /**
     * Handle the file "deleted" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function deleted(File $file)
    {
        // no-op
    }
}
