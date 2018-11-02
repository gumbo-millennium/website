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
 * Processes the metadata of the file, which retrieves document contents,
 * metadata and the number of pages.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileMetaJob implements ShouldQueue
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
     * Allow 1 minute for thumbnail job
     *
     * @var int
     */
    protected $timeout = 60;

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
        return ['pdf-process', 'pdf-meta', 'file:' . $this->file->id];
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
        $tempFile = $this->getTempFileFromPath($this->file->path, 'pdf');

        // Load PDF parser
        $parser = new PDFParser;
        $pdf = $parser->parseFile($tempFile);

        // Handle OCR contents
        $file->contents = $pdf->getText();

        // Count pages
        $file->page_count = count($pdf->getPages());

        // Get PDF metadata
        $fileDetails = $pdf->getDetails();
        $fileMeta = collect();

        // Serialize data into one-dimensional array
        foreach ($fileDetails as $property => $value) {
            $value = implode(', ', array_wrap($value));
            $fileMeta->put($property, $value);
        }

        // Store meta
        $file->file_meta = $fileMeta;

        // Close the PDF handle
        $parser = null;

        // Remve temp PDF
        if (file_exists($tempFile) && is_writeable($tempFile)) {
            @unlink($tempFile);
        }

        // Save the proposed changes
        $file->save();
    }
}
