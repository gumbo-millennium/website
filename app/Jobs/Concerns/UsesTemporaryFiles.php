<?php

namespace App\Jobs\Concerns;

use Czim\Paperclip\Contracts\AttachmentInterface;
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
    protected function getTempFileFromPath(string $path, string $ext, string $disk = 'local'): string
    {
        // Abort if file is missing
        if (!Storage::disk($disk)->exists($path)) {
            throw new \RuntimeException("Cannot find file [{$path}] on drive.");
        }

        // Open a file handle to a temporary file
        $temporaryFile = $this->getTempFile($ext);
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

        // Wait for FS to be ready
        passthru(sprintf('sync %s', escapeshellarg($temporaryFile)));

        //Report file
        passthru(sprintf('ls -lh %s', escapeshellarg($temporaryFile)));

        // Return filename
        return $temporaryFile;
    }

    /**
     * Converts attachment to file
     *
     * @param AttachmentInterface $attachment
     * @return string
     */
    protected function getTempFileFromAttachment(AttachmentInterface $attachment): string
    {
        // Throw error if not found
        if (!$attachment->exists()) {
            $model = $attachment->getModel();
            throw new \RuntimeException(sprintf(
                'The file attached on [%s] to the [%s #%s] model, does not exist',
                $attachment->name(),
                $model ? get_class($model) : 'n/a',
                optional($model)->id ?? 'n/a'
            ));
        }

        // get props
        $path = $attachment->path();
        $ext = last(explode('.', $attachment->originalFilename() ?? 'text.pdf'));
        $storage = $attachment->getStorage();

        // Make test file
        return $this->getTempFileFromPath($path, $ext, $storage);
    }

    /**
     * Returns a filename for a temporary file with the given extension
     *
     * @param string $ext Extension, without leading period.
     * @return string File path
     */
    protected function getTempFile(string $ext): string
    {
        // Generate a filename
        $tempFileName = tempnam(sys_get_temp_dir(), 'gumbo');

        // Append our wanted extension
        $fileName = "{$tempFileName}.{$ext}";

        // Move the temp file, or create a new one if moving fails
        if (!rename($tempFileName, $fileName)) {
            file_put_contents($fileName, '');
            @unlink($tempFileName);
        }

        //Report file
        passthru(sprintf('ls -lh %s', escapeshellarg($fileName)));

        // Return file name
        return $fileName;
    }

    /**
     * Attempts to delete the given file, but only if it's a temp file.
     *
     * @param string $file
     * @return void
     */
    protected function deleteTempFile(string $file): void
    {
        if (
            file_exists($file) &&
            starts_with($file, sys_get_temp_dir() &&
            is_writeable(dirname($file)))
        ) {
            @unlink($file);
        }
    }
}
