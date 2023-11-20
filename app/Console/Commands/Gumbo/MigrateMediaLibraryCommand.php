<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateMediaLibraryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gumbo:migrate-media-library';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates all files that are still on the local disk to the cloud disk.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cloudDiskName = Storage::getDefaultCloudDriver();
        $localDiskName = Storage::getDefaultDriver();

        if ($cloudDiskName == $localDiskName) {
            $this->comment('Cannot migrate data to cloud, cloud is not configured.');

            return 1;
        }

        // Assing instances
        $cloudDisk = Storage::disk($cloudDiskName);
        $localDisk = Storage::disk($localDiskName);

        // Report start
        $this->line('Migrating existing files to the cloud environment...');

        if (! $localDisk->exists('medialibrary/media')) {
            $this->error('Cannot find source directory on disk.');

            return 1;
        }

        /** @var Media $mediaItem */
        foreach ($localDisk->allFiles('medialibrary/media') as $mediaFile) {
            $mediaItemName = sprintf('%s/<info>%s</>', dirname($mediaFile), basename($mediaFile));
            $this->line("Checking file {$mediaItemName}", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $existsOnCloud = $cloudDisk->exists($mediaFile);

            if ($existsOnCloud) {
                $this->line("Media file {$mediaItemName} already exists on the cloud.", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

                continue;
            }

            // Report
            $this->line("Copying file {$mediaItemName} to the cloud.", null, OutputInterface::VERBOSITY_VERBOSE);

            // Need to copy via streams
            $localReadStream = $localDisk->readStream($mediaFile);
            if ($cloudDisk->put($mediaFile, $localReadStream)) {
                $this->info("Successfully migrated {$mediaItemName} to the cloud.", OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            $this->line("Failed to migrate {$mediaItemName} to the cloud.");
        }
    }
}
