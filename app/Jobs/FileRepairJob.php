<?php

namespace App\Jobs;

use App\File;
use App\Jobs\Concerns\ReplacesStoredFiles;
use App\Jobs\Concerns\RunsCliCommands;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Repairs PDF files, automatically fixes any PDF issues that might exist.
 * Requires GhostScript
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileRepairJob extends FileJob
{
    /**
     * Try job 3 times
     *
     * @var int
     */
    protected $tries = 3;

    /**
     * Allow 5 minutes to repair the file
     *
     * @var int
     */
    protected $timeout = 300;

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags() : array
    {
        return ['pdf-process', 'pdf-repair', 'file:' . $this->file->id];
    }

    /**
     * Execute the job.
     *
     * @return void|boolean
     */
    public function handle() : void
    {
        // Ignore if Windows
        if (!in_array(PHP_OS_FAMILY, ['Linux', 'Darwin'])) {
            return;
        }

        // Shorthand
        $file = $this->file;

        // Get a temporary file
        $pdfFile = $this->getTempFileFromPath($this->file->path, 'pdf');
        $repairedFile = $this->getTempFile('pdf');

        try {
            // Try repairing with Ghostscript
            if ($this->tryRepairWithGhostscript($pdfFile, $repairedFile)) {
                // Flag file as valid
                $this->file->addState(File::STATE_FILE_CHECKED);

                // Save the new file
                $this->replaceStoredFile($repairedFile);

                // Terminate script
                return;
            }

            // Try repairing with Ghostscript
            if ($this->tryRepairWithCairo($pdfFile, $repairedFile)) {
                // Flag file as valid
                $this->file->addState(File::STATE_FILE_CHECKED);

                // Save the new file
                $this->replaceStoredFile($repairedFile);

                // Terminate script
                return;
            }

            // Throw error
            throw new \RuntimeException(sprintf(
                'GhostScript and pdftocairo failed to repair / check the file [%d] [%s]',
                $this->file->id,
                $this->file->path
            ));
        } catch (\RuntimeException|ProcessTimedOutException $e) {
            // File repair failed, mark as broken
            $this->file->addState(File::STATE_BROKEN);
            $this->file->save();

            // Don't use error
            return;
        } finally {
            // Delete temp files
            $this->deleteTempFile($pdfFile);
            $this->deleteTempFile($repairedFile);
        }
    }

    /**
     * Attempts to repair the file using the Ghostscript binaries
     *
     * @param string $currentFile
     * @param string $newFile
     * @return bool
     */
    protected function tryRepairWithGhostscript(string $currentFile, string $newFile) : bool
    {
        echo "Attempting repair with GhostScript\n";

        try {
            $result = $this->runCliCommand([
                'gs',
                "-sOutputFile={$newFile}",
                '-sDEVICE=pdfwrite',
                '-dPDFSETTINGS=/prepress',
                $currentFile
            ], $outGhost, $errGhost, 120);

            if ($result === 0) {
                echo "GhostScript repaired the file.\n";
                return true;
            }

            echo "GhostScript failed to repair the file.\n";
            return false;
        } catch (ProcessTimedOutException $e) {
            echo "GhostScript didn't complete in time.\n";
            return false;
        }
    }

    /**
     * Attempts to repair the file using pdftocairo
     *
     * @param string $currentFile
     * @param string $newFile
     * @return bool
     */
    protected function tryRepairWithCairo(string $currentFile, string $newFile) : bool
    {

        echo "Attempting repair with pdftocairo\n";

        try {
            $result = $this->runCliCommand([
                'pdftocairo',
                '-pdf ',
                $currentFile,
                $newFile
            ], $outCairo, $errCairo, 120);

            if ($result === 0) {
                echo "pdftocairo repaired the file.\n";
                return true;
            }

            echo "pdftocairo failed to repair the file.\n";
            return false;
        } catch (ProcessTimedOutException $e) {
            echo "pdftocairo didn't complete in time.\n";
            return false;
        }
    }
}
