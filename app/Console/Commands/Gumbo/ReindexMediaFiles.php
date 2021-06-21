<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Jobs\IndexFileContents;
use App\Models\Media;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexMediaFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:index-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-indexes all files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Chunk
        $chunkCount = 0;
        $perChunkCount = 50;

        // Chunk media objects
        Media::chunk($perChunkCount, function ($medias) use ($chunkCount, $perChunkCount) {
            // Get medias
            foreach ($medias as $media) {
                $this->line(
                    "Parsing <info>{$media->file_name}</> (<comment>{$media->id}</>)...",
                    null,
                    OutputInterface::VERBOSITY_VERY_VERBOSE,
                );
                IndexFileContents::dispatchNow($media);
                $this->info("Parsed {$media->file_name}.", OutputInterface::VERBOSITY_VERBOSE);
            }

            // Get count
            $from = $chunkCount * $perChunkCount + 1;
            $to = ($chunkCount += $perChunkCount) * $perChunkCount;

            // Report
            $this->info("Parsed chunk {$chunkCount} (items <comment>${from}</> – <comment>{$to}</>).");
        });
    }
}
