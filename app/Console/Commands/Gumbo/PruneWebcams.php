<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\Webcam\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class PruneWebcams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'DOC'
            gumbo:prune-webcams
                {--force : Force removal, required if the number of files is above 15}
        DOC;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all device photo\'s that are older than 4 days (to keep weekends intact).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('force')) {
            $this->comment('--force option given, I hope you know what you\'re doing...');
        }

        $date = Date::now()->subDays(4);
        $photoDisk = Config::get('gumbo.images.disk');

        $this->line("Removing all device photos timestamped before <info>{$date->format('Y-m-d H:i:s (z)')}</>.", null, OutputInterface::VERBOSITY_VERBOSE);

        $existingFiles = Collection::make(Storage::disk($photoDisk)->allFiles(Device::STORAGE_FOLDER));

        $recentPaths = Device::where([
            ['updated_at', '>=', $date],
            ['path', '!=', null],
        ])->pluck('path');

        $filesToDelete = $existingFiles->reject(fn ($path) => $recentPaths->contains($path));

        $this->line("Found <info>{$filesToDelete->count()}</> files to delete (<comment>{$recentPaths->count()}</comment> will be kept).", null, OutputInterface::VERBOSITY_VERBOSE);

        if ($this->option('force') === false && count($filesToDelete) > 15) {
            $this->error('Too many files to delete. Use --force to force removal.');

            return Command::FAILURE;
        }

        $this->withProgressBar($filesToDelete, fn ($path) => Storage::disk($photoDisk)->delete($path));

        $this->line("Removed <info>{$filesToDelete->count()}</> photos.");

        return Command::SUCCESS;
    }
}
