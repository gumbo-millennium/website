<?php

namespace App\Observers;

use App\File;
use App\Jobs\FileArchiveJob;
use App\Jobs\FileMetaJob;
use App\Jobs\FileRepairJob;
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
        // The jobs repair the file, then convert it to a PDF/A-3 if possible
        // and then create metadata and thumbnails.
        FileRepairJob::withChain([
            new FileArchiveJob($file),
            new FileMetaJob($file),
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
