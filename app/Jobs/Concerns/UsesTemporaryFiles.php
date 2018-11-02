<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Handles creation of temporrary files by streaming from any disk.
 * Should support both local as cloud-based drives.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
trait UsesTemporaryFiles
{
    /**
     * Returns an absolute path to a file mirroring the given file on the given
     * disk, ensuring a certain extension is set.
     *
     * @param string $path Path of the file on the $disk
     * @param string $ext Extension to set, without leading period
     * @param string $disk Disk to use
     * @return string Path to the temporary file
     * @throws \RuntimeException in case any IO action fails
     */
    protected function getTempFileFromPath(string $path, string $ext, string $disk = 'local') : string
    {
        // Abort if file is missing
        if (!Storage::disk($disk)->exists($path)) {
            throw new \RuntimeException("Cannot find file [{$path}] on drive.");
        }

        // Open a file handle to a temporary file
        $temporaryFile = tempnam(sys_get_temp_dir(), 'filedump');
        $temporaryHandle = fopen($temporaryFile, 'w');

        // Abort if handle failed to grab
        if (!$temporaryHandle) {
            throw new \RuntimeException("Failed to open write handle on {$temporaryFile}");
        }

        // Get a stream of the destination file
        $fileHandle = Storage::disk($disk)->readStream($path);

        // Read the entire file
        while (!feof($fileHandle)) {
            // Read from file handle and instantly write to temp handle
            fwrite($temporaryHandle, fread($fileHandle, 512));
        }

        // Close both handles
        fclose($fileHandle);
        fclose($temporaryHandle);

        // Move file to end with a the given extension
        $endFilename = "{$temporaryFile}.{$ext}";
        rename($temporaryFile, $endFilename);

        // Return filename
        return $endFilename;
    }
}
