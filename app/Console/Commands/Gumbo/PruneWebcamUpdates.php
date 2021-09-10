<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo;

use App\Models\Webcam;
use App\Models\WebcamUpdate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class PruneWebcamUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:prune-webcams
        {--keep=20 : Number of updates to keep}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all webcam updates before the given number, also pruning the disk.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keepCount = $this->option('keep') ?? 20;

        $this->line("Removing all webcam updates after the count of <info>{$keepCount}</>.", null, OutputInterface::VERBOSITY_VERBOSE);

        $totalRemoveCount = 0;
        foreach (Webcam::all() as $webcam) {
            $removeCount = 0;
            $webcamCount = $webcam->updates()->count();
            if ($webcamCount <= $keepCount) {
                $this->line("Webcam <info>{$webcam->name}</> has <comment>{$webcamCount}</> updates, not pruning.", null, OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            $updatesToRemove = $webcam
                ->updates()
                ->latest()
                ->take($webcamCount - $keepCount)
                ->skip($keepCount)
                ->cursor();

            /** @var \App\Models\WebcamUpdate $update */
            foreach ($updatesToRemove as $update) {
                $update->delete();

                $this->line("Removed update <comment>{$update->name}</>.", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

                $removeCount++;
            }

            $this->line("Removed <info>{$removeCount}</> update(s) for <comment>{$webcam->name}</>.", null, OutputInterface::VERBOSITY_VERBOSE);

            $totalRemoveCount += $removeCount;
        }

        $this->line("Removed <info>{$totalRemoveCount}</> webcam update(s).");

        // Find all remainig updates
        $files = Storage::allFiles(WebcamUpdate::STORAGE_LOCATION);

        $fileLocations = WebcamUpdate::query()->pluck('path');

        $orphans = 0;
        foreach ($files as $file) {
            if (! $fileLocations->contains($file)) {
                Storage::delete($file);

                $this->line("Removed orphaned file <comment>{$file}</>.", null, OutputInterface::VERBOSITY_VERY_VERBOSE);

                $orphans++;
            }
        }

        $this->line("Removed <info>{$orphans}</> orphaned file(s).", null);
    }
}
