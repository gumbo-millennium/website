<?php

namespace App\Jobs;

use App\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File as LaravelFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PDFParser;

/**
 * Processes the file, meaning get the text contents, build a thumbnail and count the pages.
 */
class FileProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;

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

        // Get path to the file
        $filePath = storage_path($file->path);

        // Load PDF parser
        $parser = new PDFParser;
        $pdf = $parser->parseFile($file->path);

        // Handle OCR contents
        $file->contents = $pdf->getText();

        // Count pages
        $file->pageCount = count($pdf->getPages());

        // Get screenshot of first page using Imagick
        if (extension_loaded('imagick')) {
            $thumbnailFile = tempnam(sys_get_temp_dir(), 'pdfconf');

            // Load imagick to convert the file
            $im = new Imagick;
            $im->setResolution(512, 256);
            $im->readimage("{$filePath}[0]");
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
        }

        // Save the proposed changes
        $file->save();
    }
}
