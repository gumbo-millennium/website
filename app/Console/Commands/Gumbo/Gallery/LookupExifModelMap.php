<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Gallery;

use App\Services\GalleryExifService;
use Illuminate\Console\Command;

class LookupExifModelMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:gallery:exif:lookup
        {make : The make of the device}
        {model : The model of the device}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Looks up a make and mode combination to their marketing name.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GalleryExifService $galleryExifService)
    {
        // Get make and model
        $make = $this->argument('make');
        $model = $this->argument('model');

        $results = $galleryExifService->determineDisplayMakeAndModel($make, $model);

        $this->line("Input make: <fg=yellow>{$make}</>");
        $this->line("Input model: <fg=yellow>{$model}</>");
        $this->line("Found make: <fg=yellow>{$results['make']}</>");
        $this->line("Found model: <fg=yellow>{$results['model']}</>");

        $this->line("\nDisplay as: <fg=cyan>{$results['make']} {$results['model']}</>");

        return 0;
    }
}
