<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\FileMetaJob;
use App\Jobs\FileThumbnailJob;
use App\Models\File;

class FileObserver
{
    /**
     * Handle the file "created" event.
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
}
