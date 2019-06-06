<?php

namespace App\Jobs;

use App\Models\File;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PDFParser;
use App\Jobs\Concerns\RunsCliCommands;

/**
 * Processes the metadata of the file, which retrieves document contents,
 * metadata and the number of pages.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileMetaJob extends FileJob
{
    /**
     * Try job 3 times
     *
     * @var int
     */
    protected $tries = 3;

    /**
     * Allow 1 minute to get metadata
     *
     * @var int
     */
    protected $timeout = 60;

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

        // Extract meta
        $this->getPdfContent($tempFile);

        // Update state, if present
        if ($this->file->contents) {
            $file->addState(File::STATE_HAS_CONTENT);
        }

        // Get PDF metadata, using pdfinfo
        $this->getMetadata($file);

        // Update state
        if ($this->file_meta) {
            $file->addState(File::STATE_HAS_META);
        }

        // Remve temp PDF
        $this->deleteTempFile($tempFile);

        // Save the proposed changes
        $file->save();
    }

    /**
     * Handle PDF content, which is extracted by the PDF parser.
     *
     * @param string $filePath
     * @return void
     */
    protected function getPdfContent(string $filePath) : void
    {
        // Load PDF parser
        $parser = new PDFParser();
        $pdf = $parser->parseFile($filePath);

        // Handle OCR contents
        $this->file->contents = $pdf->getText();
    }

    /**
     * Uses exiftool to hide sensitive metadata
     *
     * @param string $filePath
     * @return void
     */
    protected function scrubPdfMeta(string $filePath) : void
    {
        // Get name of the owner
        $ownerName = optional($this->file->owner)->pdf_name ?? config('app.name');

        // Get the name of the system
        $systemName = sprintf(
            '%s - %s',
            config('app.name'),
            trans('files.title') !== 'files.title' ? trans('files.title') : 'File System'
        );

        // Run filter command, which removes some sensitive data from
        // the file. SEE /PRIVACY.md
        $removeList = [
            'PDF:Author' => $ownerName,
            'PDF:Creator' => $ownerName,
            'PDF:Producer' => $systemName,
            ''
        ];

        // Build command. The structure is [exittool + commands] + [fields] + [filename].
        $command = array_merge([
            'exiftool',
            '-overwrite_original', // Don't create file_original
            '-preserve', // Don't alter timestamps
            $filePath
        ], collect($removeList)->map(function ($value) {
            return sprintf('-%s=%s', $value[0], escapeshellarg($value[1]));
        }));

        // Run remove command
        $ok = $this->runCliCommand($command, $stdout, $stderr);

        // Only log if reducing data failed
        if ($ok) {
            return;
        }

        logger()->notice('Failed to remove metadata from [{filename}].', [
            'filename' => $this->file->filename,
            'file' => $this->file,
            'output' => $stdout . PHP_EOL . $stderr,
        ]);
    }

    /**
     * Retrieves metadata from listed file using exiftool and
     * saves it to the file.
     *
     * @param string $filePath
     * @return void
     */
    protected function getMetadata(string $filePath) : void
    {
        // Build request list
        $requestList = collect([
            'PDF:all',
            'XMP-pdfaid:all',
            'XMP-pdf:all'
        ])->map(function ($value) {
            return str_start($value, '-');
        });

        // Build command. The structure is [exittool + commands] + [fields] + [filename].
        $command = array_merge([
            'exiftool',
            '-a',
            '-G1',
            '-json'
        ], $requestList, [
            $filePath
        ]);

        // Run meta command
        $ok = $this->runCliCommand($command, $stdout, $stderr);

        // Check if everything went OK
        if (!$ok) {
            logger()->notice('Failed to retrieve metadata from [{filename}].', [
                'filename' => $this->file->filename,
                'file' => $this->file,
                'output' => $stdout . PHP_EOL . $stderr,
            ]);

            // Abort
            return;
        }

        // Decode JSON
        $metaFields = json_decode($stdout);

        // Abort on error
        if (json_last_error() !== JSON_ERROR_NONE) {
            logger()->notice('Failed to parse JSON metadata for [{filename}].', [
                'filename' => $this->file->filename,
                'file' => $this->file,
                'output' => $stdout,
            ]);

            return;
        }

        // Serialize data into one-dimensional array
        foreach ($metaFields as $property => $value) {
            $value = implode(', ', array_wrap($value));
            $fileMeta->put($property, $value);
        }

        // Store meta
        $this->file->file_meta = $fileMeta;
    }
}
