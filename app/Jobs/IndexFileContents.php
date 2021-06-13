<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Str;
use App\Jobs\Concerns\RunsCliCommands;
use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use JsonException;
use Smalot\PdfParser\Parser as PDFParser;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Throwable;

class IndexFileContents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use RunsCliCommands;
    use SerializesModels;

    protected Media $media;

    /**
     * Try job 3 times.
     *
     * @var int
     */
    protected $tries = 3;

    /**
     * Allow 1 minute to get metadata.
     *
     * @var int
     */
    protected $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Media $media)
    {
        // Media
        $this->media = $media;
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['pdf-process', 'pdf-meta', 'media:' . $this->media->id];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Filesystem $filesystem)
    {
        // Get the media and the mime type
        $media = $this->media;
        if ($media->mime_type !== 'application/pdf') {
            logger()->notice(
                "Will not process file of type {$media->mime_type} present on {media}",
                compact('media')
            );

            return;
        }

        // Prep a tempfile
        $tempfile = \tempnam(\sys_get_temp_dir(), 'pdf-');
        $filesystem->copyFromMediaLibrary($media, $tempfile);

        // Compare file size
        if (\filesize($tempfile) !== $media->size) {
            logger()->warning(
                'Copying media to temp file resulted in different sizes',
                compact('media', 'tempfile')
            );

            return;
        }

        // Process the PDF contents
        try {
            $contents = $this->processPdf($tempfile);
            $media->setCustomProperty('file-content', $contents);
        } catch (Throwable $exception) {
            logger()->error(
                'Retrieving PDF contents failed: {exception}',
                compact('media', 'exception')
            );
        }

        // Process the PDF's meta
        try {
            $meta = $this->processMeta($tempfile);
            $media->setCustomProperty('file-meta', $meta);
        } catch (Throwable $exception) {
            logger()->error(
                'Retrieving PDF contents failed: {exception}',
                compact('media', 'exception')
            );
        }

        // Delete tempfile
        if (\file_exists($tempfile)) {
            unlink($tempfile);
        }

        // Save changes to model
        $media->save();

        // Log result
        logger()->notice(
            'Updated PDF metadata of {media}',
            compact('media')
        );
    }

    private function processPdf(string $file): string
    {
        // Load PDF parser
        $parser = new PDFParser();
        $pdf = $parser->parseFile($file);

        // Handle OCR contents
        return $pdf->getText();
    }

    private function processMeta(string $file): ?array
    {
        // Check if exiftool is available
        $hasTool = Cache::remember('cli.pff.has-exiftool', now()->addDay(), function () {
            $command = ['which', 'exiftool'];
            $result = $this->runCliCommand($command);

            return (bool) $result;
        });

        if (! $hasTool) {
            logger()->notice('Exif tool not installed on device');

            return null;
        }

        // Build request list
        $requestList = collect([
            'PDF:all',
            'XMP-pdfaid:all',
            'XMP-pdf:all',
        ])->map(static fn ($value) => Str::start($value, '-'));

        // Build command. The structure is [exittool + commands] + [fields] + [filename].
        $command = array_merge(
            ['exiftool'],
            ['-a', '-G1', '-json'],
            $requestList->toArray(),
            [$file]
        );

        // Run meta command
        $ok = $this->runCliCommand($command, $stdout, $stderr);

        // Check if everything went OK
        if (! $ok) {
            echo 'Exif failed';
            logger()->notice('Failed to retrieve metadata from [{filename}].', [
                'filename' => $this->media->name,
                'media' => $this->media,
                'output' => $stdout . PHP_EOL . $stderr,
            ]);

            // Abort
            return null;
        }

        // Allow empty list
        if (empty($stdout)) {
            return null;
        }

        // Decode JSON
        try {
            $metaFields = json_decode($stdout, true, 64, \JSON_THROW_ON_ERROR);

            // Serialize data into one-dimensional array
            foreach ($metaFields as $property => $value) {
                $value = implode(', ', array_wrap($value));
                $fileMeta->put($property, $value);
            }

            // Return the file
            return $metaFields;
        } catch (JsonException $e) {
            logger()->notice('Failed to parse JSON metadata for [{filename}].', [
                'filename' => $this->media->name,
                'media' => $this->media,
                'output' => $stdout,
            ]);
        }

        return null;
    }
}
