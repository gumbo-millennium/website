<?php

namespace App\Jobs;

use App\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\File as LaravelFile;
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

        // Create tempfile
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'pdf-process');
        $tempPdfHandle = fopen($tempPdfFile, 'w');

        if (!$tempPdfHandle) {
            throw new \RuntimeException("Failed to open write handle on {$tempPdfFile}");
        }

        // Get a stream
        $pdfStream = Storage::readStream($file->path);

        // Write entire stream to harddrive
        while (!feof($pdfStream)) {
            $buffer = fread($pdfStream, 1024);  // Write small bits
            fwrite($tempPdfHandle, $buffer);
        }

        // Close both handles
        fclose($pdfStream);
        fclose($tempPdfHandle);

        // Move file to end with a .pdf file
        rename($tempPdfFile, "{$tempPdfFile}.pdf");
        $tempPdfFile .= '.pdf';

        // Load PDF parser
        $parser = new PDFParser;
        $pdf = $parser->parseFile($tempPdfFile);

        // Handle OCR contents
        $file->contents = $pdf->getText();

        // Count pages
        $file->page_count = count($pdf->getPages());

        // Metadata
        $fileDetails = $pdf->getDetails();
        $fileMeta = collect();
        foreach ($fileDetails as $property => $value) {
            $value = implode(', ', array_wrap($value));
            $fileMeta->put($property, $value);
        }

        // TODO add meta
        // $file->meta = $fileMeta;

        // Get screenshot of first page using Imagick
        if (extension_loaded('imagick')) {
            $thumbnailFile = tempnam(sys_get_temp_dir(), 'pdfconf');

            // Load imagick to convert the file
            $im = new \Imagick;
            $im->setResolution(512, 256);
            $im->readimage("{$tempPdfFile}[0]");
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

        // Remve temp PDF
        if (file_exists($tempPdfFile) && is_writeable($tempPdfFile)) {
            @unlink($tempPdfFile);
        }

        // Save the proposed changes
        $file->save();
    }
}
