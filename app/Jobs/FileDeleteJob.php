<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Deletes a file from the database AND from the filesystem
 *
 * @author Roelof Roos <github@roelof.io>
 */
class FileDeleteJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The file being deleted
     *
     * @param File $file
     */
    protected $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = $this->file;

        // Create a 'file deleted' file, this ensures a 410 is sent, insead of a 404.
        DeletedFile::createFromFile($file);

        // Delete the file
        $file->forceDelete();

        // Remove the file from the storage
        if ($file->path !== null) {
            Storage::delete(File::STORAGE_DIR, $file->path);
        }

        // Remove the thumbnail, if any
        if ($file->thumbnail !== null) {
            Storage::delete('thumbnails', $file->thumbnail);
        }

        // Done
    }
}
