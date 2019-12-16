<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\File;
use App\Jobs\Concerns\ReplacesStoredFiles;
use App\Jobs\Concerns\RunsCliCommands;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Converts PDF file to the PDF/A-3 standard. Requires Ghostscript.
 * Cannot typically error.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileArchiveJob extends FileJob
{
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
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['pdf-process', 'pdf-archive', 'file:' . $this->file->id];
    }

    /**
     * Execute the job.
     *
     * @return void|boolean
     */
    public function handle(): void
    {
        // Ignore if Windows
        if (!in_array(PHP_OS_FAMILY, ['Linux', 'Darwin'])) {
            return;
        }

        // Shorthand
        $file = $this->file;

        // Skip if the file is already PDF-A
        if ($file->hasState(File::STATE_PDFA)) {
            return;
        }

        // Get a temporary file
        try {
            $pdfFile = $this->getTempFileFromAttachment($this->file->file);
            $archiveFile = $this->getTempFile('pdf');
        } catch (RuntimeException $e) {
            return;
        }

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
            ], null, null, $this->timeout * .8);

            // If the exit code is non-zero, log it and exit.
            if ($exitCode !== 0) {
                printf("Conversion command failed for [%s] (%d).\n", $this->file->title, $this->file->id);
                return;
            }

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
