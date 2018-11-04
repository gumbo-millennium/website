<?php

namespace App\Jobs;

use App\File;
use App\Jobs\Concerns\ReplacesStoredFiles;
use App\Jobs\Concerns\RunsCliCommands;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Converts PDF file to the PDF/A-3 standard. Requires Ghostscript.
 * Cannot typically error.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileArchiveJob implements ShouldQueue
{
    use
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        ReplacesStoredFiles,
        RunsCliCommands,
        SerializesModels,
        UsesTemporaryFiles;

    /**
     * Acting file
     *
     * @var App\File
     */
    protected $file;

    /**
     * Try job 3 times
     *
     * @var int
     */
    protected $tries = 3;

    /**
     * Allow 3 minutes to archive
     *
     * @var int
     */
    protected $timeout = 180;

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
        return ['pdf-process', 'pdf-archive', 'file:' . $this->file->id];
    }

    /**
     * Execute the job.
     *
     * @return void|boolean
     */
    public function handle()
    {
        // Ignore if Windows
        if (!in_array(PHP_OS_FAMILY, ['Linux', 'Darwin'])) {
            return;
        }

        // Shorthand
        $file = $this->file;

        // Get a temporary file
        $pdfFile = $this->getTempFileFromPath($this->file->path, 'pdf');
        $archiveFile = $this->getTempFile('pdf');

        try {
            // Try archiving the file
            $exitCode = $this->runCliCommand([
                'gs',
                '-dPDFA=3',
                '-dBATCH',
                '-dNOPAUSE',
                '-sProcessColorModel=DeviceRGB',
                '-sDEVICE=pdfwrite',
                '-sPDFACompatibilityPolicy=1',
                "-sOutputFile={$archiveFile}",
                $pdfFile
            ], $out, $err, $this->timeout * .8);

            // If the exit code is non-zero, log it and exit.
            if ($exitCode !== 0) {
                printf("Conversion command failed for [%s] (%d).\n", $this->file->title, $this->file->id);
                return;
            }

            // Flag file as having a PDF/A compliant file
            $this->file->addState(File::STATE_PDFA);

            // Again, save the new file
            $this->replaceStoredFile($archiveFile);
        } catch (ProcessTimedOutException $e) {
            // Process timed out, but that's no problem.
            printf("Conversion command timed out for [%s] (%d).\n", $this->file->title, $this->file->id);
        } finally {
            // Delete temp files
            $this->deleteTempFile($pdfFile);
            $this->deleteTempFile($archiveFile);
        }
    }
}
