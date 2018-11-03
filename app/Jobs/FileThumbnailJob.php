<?php

namespace App\Jobs;

use App\File;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PDFParser;

/**
 * Makes a thumbnail of the file's first page.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UsesTemporaryFiles;

    /**
     * Acting file
     *
     * @var App\File
     */
    protected $file;

    /**
     * Try thumbnailing 3 times
     *
     * @var int
     */
    protected $tries = 3;

    /**
     * Allow 5 seconds for thumbnail job
     *
     * @var int
     */
    protected $timeout = 5;

    /**
     * Create a new job instance.
     *
     * @param File $file File to process
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags() : array
    {
        return ['pdf-process', 'pdf-thumbnail', 'file:' . $this->file->id];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : void
    {
        // Make sure file is valid
        $file = $this->file;
        if (!$file) {
            return;
        }

        // Get a temporary file
        $pdfFile = $this->getTempFileFromPath($this->file->path, 'pdf');

        // Get a good temporary file name to use
        $thumbnailTempFile = tempnam(sys_get_temp_dir(), 'pdfconf');
        if (empty($thumbnailTempFile)) {
            throw new \RuntimeException('Failed to obtain a temporary file name');
        }

        // Delete the temp file and make our own
        $thumbnailFile = "{$thumbnailTempFile}.jpg";
        @unlink($thumbnailTempFile);
        file_put_contents($thumbnailFile, '');

        // Wait for the system to be ready (about 500ms should do)
        usleep(500 * 1000);

        // Build the convert command
        $command = sprintf(
            'convert -density 600 %s[1] -colorspace RGB -resample 300 %s',
            escapeshellarg($pdfFile),
            escapeshellarg($thumbnailFile)
        );

        // DEEEEBUG
        printf("Running command [%s]\n", $command);

        try {
            // Fire up the command
            exec($command, $result, $code);

            // Print the result
            printf("Command completed with [%d]\n\n=========\n> %s\n=========\n", $code, implode("\n> ", $result));

            // Abort if code != 0
            if ($code !== 0) {

                $dumpPdf = Storage::putFile('dumps', new LaravelFile($pdfFile));
                $dumpJpg = Storage::putFile('dumps', new LaravelFile($thumbnailFile));

                printf(
                    "Files stored in app, available under [%s] and [%s]\n",
                    $dumpPdf,
                    $dumpJpg
                );

                $errorMessage = sprintf(
                    "Failed to convert PDF using [%s]. Received %d:\n\n%s",
                    $command,
                    $code,
                    implode(PHP_EOL, $result)
                );
                throw new \RuntimeException($errorMessage, $code);
            }

            // Store file
            $thumnail = Storage::putFile('thumbnails', new LaravelFile($thumbnailFile));

            // Assign thumbnail
            $file->thumbnail = $thumnail;

            // Save file
            $file->save();
        } catch (\RuntimeException $e) {
            // Bubble that shit up
            throw $e;
        } finally {
            // Delete generated thumbnail file, if possible.
            // Ignore if file deletion fails. Unix should auto-clean the temp dir
            if (file_exists($thumbnailFile) && is_writeable(dirname($thumbnailFile))) {
                @unlink($thumbnailFile);
            }

            // Remve temp PDF
            if (file_exists($pdfFile) && is_writeable($pdfFile)) {
                @unlink($pdfFile);
            }
        }
    }
}
