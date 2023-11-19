<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Enums\PhotoVisibility;
use App\Models\Gallery\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class PruneGalleryFilepondCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        gumbo:prune-gallery-filepond
            {--prune : Prune all photo models that point to non-existing files}
            {--clean : Remove files not associated with photos}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all filepond files that have not been used in a while.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find all pending uploads
        $pendingPhotos = Photo::query()
            ->where(function ($query) {
                $query
                    ->where('visibility', PhotoVisibility::Pending)
                    ->where('path', 'LIKE', Config::get('gumbo.gallery.filepond.path') . '%');
            })
            ->get()
            ->keyBy('path');

        $this->line("Fetched <info>{$pendingPhotos->count()}</info> photo models.", null, OutputInterface::VERBOSITY_VERBOSE);

        // Get all existing files
        $disk = Storage::disk(Config::get('gumbo.gallery.filepond.disk'));
        $existingFiles = $disk->allFiles(Config::get('gumbo.gallery.filepond.path'));

        // Get photos to remove
        $expiredAfter = Date::now()->subHours(4)->getTimestamp();
        $filesDeleted = [];
        $filesLeft = [];

        // Report back
        $foundFilesCount = count($existingFiles);
        $this->line("Fetched <info>{$foundFilesCount}</info> files.", null, OutputInterface::VERBOSITY_VERBOSE);

        // Loop through all files, removing expired ones and flagging
        // their associated photo models
        foreach ($existingFiles as $file) {
            if ($disk->lastModified($file) > $expiredAfter) {
                $filesLeft[] = $file;

                continue;
            }

            $filesDeleted[] = $file;

            $this->line("Found <info>{$file}</info>", null, OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $disk->delete($filesDeleted);

        $deletedFilesCount = count($filesDeleted);
        $this->line("Deleted <info>{$deletedFilesCount}</> files from disk", null, OutputInterface::VERBOSITY_VERBOSE);

        $deletedPhotoModels = $pendingPhotos
            ->only($filesDeleted)
            ->each->forceDelete()
            ->count();

        $this->line("Deleted <info>{$deletedPhotoModels}</> photo models that had their files pruned", null, OutputInterface::VERBOSITY_VERBOSE);

        if ($this->option('prune')) {
            $deletedPhotoModels = $pendingPhotos
                ->reject(fn (Photo $photo) => in_array($photo->path, $filesLeft, true))
                ->each->forceDelete()
                ->count();

            $this->line("Deleted <info>{$deletedPhotoModels}</> photo models that were missing files", null, OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $this->line('Skipped pruning of photo models', null, OutputInterface::VERBOSITY_VERBOSE);
        }

        if ($this->option('clean')) {
            if (empty(Config::get('gumbo.gallery.filepond.path'))) {
                $this->error('Filepond path is not configured.');

                return Command::FAILURE;
            }

            $orphanFiles = Collection::make($filesLeft)
                ->reject(fn (string $path) => $pendingPhotos->has($path));

            $disk->delete($orphanFiles->values()->all());

            $this->line("Cleaned <info>{$orphanFiles->count()}</> files that were not associated to a photo", null, OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $this->line('Skipped cleaning of orphan files', null, OutputInterface::VERBOSITY_VERBOSE);
        }

        return Command::SUCCESS;
    }
}
