<?php

namespace App\Jobs;

use App\File;
use App\Jobs\Concerns\RunsCliCommands;
use App\Jobs\Concerns\UsesTemporaryFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    use
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        RunsCliCommands,
        SerializesModels,
        UsesTemporaryFiles;

    /**
     * Imag size of thumbnails
     *
     * @var string
     */
    const RESIZE_WIDTH = '300x400';

    const ERROR_MSG = <<<MSG
Failed to make thumbnail [%s].

Outputs follow
======== stdout  ========

%s

======== stderr  ========

%s

=========================
MSG;
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
     * Allow 60 seconds for Ghostscript and Imagick to get a thumbnail.
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
        return ['pdf-process', 'pdf-thumbnail', 'file:' . $this->file->id];
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
        $thumbnailFile = $this->getTempFile('jpeg');

        try {
            echo "Building thumbnail\n";
            $result = $this->runCliCommand([
                'convert',
                '-flatten',
                '-background', 'white',
                '-alpha', 'remove',
                '-density', '100',
                "{$pdfFile}[0]",
                '-colorspace', 'RGB',
                '-scale', self::RESIZE_WIDTH,
                '-gravity', 'center',
                '-extent', self::RESIZE_WIDTH,
                '-resize', '80%',
                '-flatten',
                '-resize', '125%',
                $thumbnailFile,
            ], $procOut, $procErr);

            if ($result !== 0) {
                throw new \RuntimeException(sprintf(
                    self::ERROR_MSG,
                    $file->title,
                    $procOut,
                    $procErr
                ), 1);
            }

            // Store and assign thumbnail
            $file->thumbnail = Storage::putFile(
                'thumbnails',
                new LaravelFile($thumbnailFile)
            );
            $file->addState(File::STATE_HAS_THUMBNAIL);

            // Save file
            $file->save();
        } catch (\RuntimeException $e) {
            // Bubble that shit up
            throw $e;
        } finally {
            $this->deleteTempFile($thumbnailFile);
            $this->deleteTempFile($pdfFile);
        }
    }
}
