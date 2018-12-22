<?php

namespace App\Jobs\Concerns;

use App\File;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Support\Facades\Storage;

/**
 * Safely replaces stored files and updates the File object.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait ReplacesStoredFiles
{
    /**
     * Replaces the current file path with the new file in a rather safe fashion.
     * The old file is deleted after update.
     *
     * @param string $newFile
     * @return void
     */
    protected function replaceStoredFile(string $newFile) : void
    {
        // Get paths
        $oldPath = $this->file->path;
        $newPath = Storage::put(File::STORAGE_DIR, new LaravelFile($newFile));

        // Validate put action
        if (!$newPath || !Storage::exists($newPath)) {
            return;
        }

        // Assign paths
        $this->file->path = $newPath;
        $this->file->save();

        // Delete old file
        Storage::delete($oldPath);
    }
}
