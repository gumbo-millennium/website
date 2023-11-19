<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenor;

use App\Jobs\Tenor\SearchGifJob;
use App\Services\TenorGifService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class PreloadGifsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        tenor:preload-gifs
            {--prune : Prune all gifs that are currently present}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preload all gifs in the public gif storage directory.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TenorGifService $gifService)
    {
        if (! $gifService->getApiKey()) {
            $this->error('No API key configured for Tenor.');

            return 1;
        }

        if ($this->option('prune')) {
            $disk = $gifService->getDisk();
            $files = $disk->allFiles($gifService->getGifBaseDirectory());
            $this->comment('Pruning all gifs...');

            if ($files) {
                $this->withProgressBar($files, fn ($file) => $disk->delete($file));
                $this->newLine();
                $this->info('Pruned all gifs.');
            } else {
                $this->info('No gifs to prune.');
            }
        }

        $groups = array_keys($gifService->getTerms());
        $jobs = [];
        foreach ($groups as $group) {
            $this->line("Preparing for <comment>{$group}</>...");
            $jobs[] = new SearchGifJob($group);
        }

        $this->line('Dispatching jobs...');
        Bus::batch($jobs)->dispatch();

        return 0;
    }
}
