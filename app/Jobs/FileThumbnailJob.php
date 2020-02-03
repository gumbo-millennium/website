<?php

declare(strict_types=1);

namespace App\Jobs;

use Czim\Paperclip\Contracts\AttachmentInterface;
use RuntimeException;

/**
 * Makes a thumbnail of the file's first page.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileThumbnailJob extends FileJob
{
    /**
     * Imag size of thumbnails
     */
    private const RESIZE_WIDTH = '300x400';

    /**
     * Try job 3 times
     * @var int
     */
    protected $tries = 3;

    /**
     * Allow 60 seconds for Ghostscript and Imagick to get a thumbnail.
     * @var int
     */
    protected $timeout = 60;

    /**
     * Get the tags that should be assigned to the job.
     * @return array
     */
    public function tags(): array
    {
        return ['pdf-process', 'pdf-thumbnail', 'file:' . $this->file->id];
    }

    /**
     * Execute the job.
     * @return void|boolean
     */
    public function handle(): void
    {
        // Ignore if Windows
        if (!in_array(PHP_OS_FAMILY, ['Linux', 'Darwin'])) {
            return;
        }

        // Abort if the `convert` command is missing
        $convertExists = $this->runCliCommand(['which', 'convert']);
        if ($convertExists !== 0) {
            logger()->warning('Convert command missing from system! Please install ImageMagick');
            return;
        }

        // Shorthand
        $file = $this->file;

        // Check for sanity
        if (!$file->thumbnail instanceof AttachmentInterface) {
            logger()->error('Somehow, {file} is missing an attachment on {thumbnail}', [
                'file' => $file,
                'thumbnail' => $file->thumbail
            ]);
            return;
        }

        // Get a temporary file
        try {
            $pdfFile = $this->getTempFileFromAttachment($this->file->file);
            $thumbnailFile = $this->getTempFile('jpeg');
        } catch (RuntimeException $e) {
            return;
        }

        try {
            echo "Building thumbnail\n";
            $command = [
                'convert',
                '-flatten',
                '-background', 'white',
                '-alpha', 'remove',
                '-density', '100',
                "{$pdfFile}[0]",
                '-colorspace', 'RGB',
                '-scale', self::RESIZE_WIDTH,
                '-gravity', 'center',
                '-extent', self::RESIZE_WIDTH,
                '-resize', '80%',
                '-flatten',
                '-resize', '125%',
                $thumbnailFile,
            ];

            $result = $this->runCliCommand($command, $procOut, $procErr);

            if ($result !== 0) {
                logger()->warning('Failed to create a screenshot of the PNG for {file}.', [
                    'file' => $file,
                    'command' => $command,
                    'output' => [
                        'normal' => $procOut,
                        'error' => $procErr
                    ],
                    'exit-code' => $result
                ]);
                return;
            }

            // Store and assign thumbnail
            $file->thumbail = new \SplFileInfo($thumbnailFile);

            // Save file
            $file->save();
        } catch (\RuntimeException $e) {
            // Make a warning
            logger()->warning('Recieved exception whilst thumbnailing {file}.', [
                'file' => $file,
                'exception' => $e
            ]);

            // Bubble that shit up
            throw $e;
        } finally {
            $this->deleteTempFile($thumbnailFile);
            $this->deleteTempFile($pdfFile);
        }
    }
}
