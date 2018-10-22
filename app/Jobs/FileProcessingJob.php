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

class FileProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        if (!$file) {
            return;
        }

        $filePath = storage_path($file->path);

        // TODO Handle OCR contents

        // TODO Count pages
        if (extension_loaded('imagick')) {
            // Load imagick
            $im = new Imagick();
            $im->pingImage("{$filePath}[0]");
            $pageCount = $im->getNumberImages();
            $im->clear();
            $im->destroy();

            // Write results
            $file->pageCount = $pageCount;
            $file->save();
        }

        // TODO Add thumbnail
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
            $thumnail = Storage::putFile('thumbnails', new LaravelFile('/path/to/photo'));
            $file->thumbnail = $thumnail;
            $file->save();
        }
    }
}
