<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
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

        // Alter medialibrary package to use the local disk
        Config::set('medialibrary.disk_name', $localDiskName);

        // Assing instances
        $cloudDisk = Storage::disk($cloudDiskName);
        $localDisk = Storage::disk($localDiskName);

        // Report start
        $this->line('Migrating existing files to the cloud environment...');

        /** @var Media $mediaItem */
        foreach (Media::query()->cursor() as $mediaItem) {
            $mediaItemName = "<info>{$mediaItem->id}</> (<comment>{$mediaItem->file_name}</>)";
            $this->line("Checking file ${mediaItemName}", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $basicPath = $mediaItem->getPath();
            $existsLocally = $localDisk->exists($basicPath);
            $existsOnCloud = $cloudDisk->exists($basicPath);
            if ($existsLocally && $existsOnCloud) {
                $this->line("Media file for ${mediaItemName} appears to be missing!");
                $this->line("Media path: <info>{$basicPath}</>", null, OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            if ($existsOnCloud) {
                $this->line("Media file for {$mediaItemName} already exists on the cloud.", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

                continue;
            }

            // Report
            $this->line("Migrating file {$mediaItemName} to the cloud.", null, OutputInterface::VERBOSITY_VERBOSE);

            // Need to copy via streams
            $localReadStream = $localDisk->readStream($basicPath);
            if ($cloudDisk->putStream($basicPath, $localReadStream)) {
                $this->info("Successfully migrated file {$mediaItemName} to the cloud.", OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            $this->line("Failed to migrate file {$mediaItemName} to the cloud.");
        }
    }
}
