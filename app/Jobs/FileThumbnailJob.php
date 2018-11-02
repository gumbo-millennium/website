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
        // Make sure file is valid, and imagick is available
        $file = $this->file;
        if (!$file || extension_loaded('imagick')) {
            return;
        }

        // Get a temporary file
        $pdfFile = $this->getTempFileFromPath($this->file->path, 'pdf');

        // Get screenshot of first page using Imagick
        $thumbnailFile = tempnam(sys_get_temp_dir(), 'pdfconf');

        // Load imagick to convert the file
        $im = new \Imagick;
        // $im->setResolution(512, 256);
        $im->readimage("{$pdfFile}[0]");
        $im->setImageFormat('jpeg');
        $im->writeImage($thumbnailFile);
        $im->clear();
        $im->destroy();

        // Store file
        $thumnail = Storage::putFile('thumbnails', new LaravelFile($thumbnailFile));
        $file->thumbnail = $thumnail;

        // Delete generated thumbnail file, if possible.
        // Ignore if file deletion fails. Unix should auto-clean the temp dir
        if (file_exists($thumbnailFile) && is_writeable(dirname($thumbnailFile))) {
            @unlink($thumbnailFile);
        }

        // Remve temp PDF
        if (file_exists($pdfFile) && is_writeable($pdfFile)) {
            @unlink($pdfFile);
        }

        // Save the proposed changes
        $file->save();
    }
}
