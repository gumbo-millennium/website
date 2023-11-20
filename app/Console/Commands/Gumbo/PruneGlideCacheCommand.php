<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class PruneGlideCacheCommand extends Command
{
    private const CACHE_DURATION = 'P7D';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        gumbo:prune-glide-cache
            {--force : Actually delete the files}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Glide cache images that haven\'t been modified in a while.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all existing files
        $disk = Storage::disk(Config::get('gumbo.glide.cache-disk'));
        $path = Config::get('gumbo.glide.cache-path');

        if (empty($path) || $path == '/') {
            $this->error('Glide cache path is not configured or is too broad.');
            $this->line('This might delete files that we want to keep, preventing!');

            return Command::FAILURE;
        }

        // Find all files
        $existingFiles = $disk->allFiles($path);

        // Get expiration date
        $pruneBefore = Date::now()->sub(self::CACHE_DURATION)->getTimestamp();

        // Prep a list
        $toDelete = [];

        // Iterate over files
        foreach ($existingFiles as $file) {
            // Get file modification date
            $modifiedAt = $disk->lastModified($file);

            // If file is older than pruneBefore, flag it for deletion
            if ($modifiedAt >= $pruneBefore) {
                continue;
            }

            $toDelete[] = $file;

            $this->line("Would delete <comment>{$file}</comment>", null, OutputInterface::VERBOSITY_VERBOSE);
        }

        // Check if empty
        if (empty($toDelete)) {
            $this->info('No files to delete.');

            return Command::SUCCESS;
        }

        // Delete files
        $deletedCount = count($toDelete);

        // Only report
        if (! $this->option('force')) {
            $this->line("Would delete <info>{$deletedCount}</info> files.");
            $this->line('Use <comment>--force</comment> to actually delete them.');
            $this->line('Use <comment>--verbose</comment> to see which files would be deleted.');

            return Command::SUCCESS;
        }

        // Delete files
        $disk->delete($toDelete);

        $this->line("Deleted <info>{$deletedCount}</info> files.");

        return Command::SUCCESS;
    }
}
