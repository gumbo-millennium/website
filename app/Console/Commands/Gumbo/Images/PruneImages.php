<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Images;

use App\Console\Commands\Gumbo\Images\Traits\UsesModelsWithImages;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PruneImages extends Command
{
    use UsesModelsWithImages;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:images:prune
        {model : Model class to prune}
        {--all : Prune all models, instead of just one}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all images in the base folder that should not be in there.';

    /**
     * Image service used to determine the image location.
     */
    protected ImageService $imageService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ImageService $imageService)
    {
        $this->imageService = $imageService;

        $modelArg = $this->argument('model');
        $allOption = $this->option('all');

        // parse options
        if ($allOption) {
            $this->info('Acting on <fd=red>all models</>');
            $models = $this->availableImageModels();
        } else {
            $this->line("Acting on <info>{$modelArg}</>");
            $models = [$modelArg];
        }

        // Find all FOLDERS of all attachments
        $allValidFolders = [];
        foreach ($models as $modelArg) {
            $this->runOnAllModelImages($modelArg, fn ($model, $unused, $value) => $allValidFolders[] = $imageService->getStoragePathForModelAttribute($model, $value));
        }
        $this->line(sprintf('Found <info>%d</> directories to keep.', $allValidFolders));

        // For all folders, create an entry for each size
        $allValidFiles = [];
        foreach ($allValidFolders as $folder) {
            foreach ($imageService->getImageSizes() as $size) {
                $allValidFiles[] = "{$folder}/{$size}.webp";
            }
        }
        $this->line(sprintf('Expanded to <info>%d</> files to keep.', $allValidFiles));

        // Loads all files from the storage
        $allFiles = Storage::disk($imageService->getStorageDiskName())->allFiles($imageService->getStorageBasePath());
        $this->line(sprintf('Found <info>%d</> files in total.', count($allFiles)));

        // Diff the files
        $filesToDelete = array_diff($allFiles, $allValidFiles);
        $this->line(sprintf('Found <info>%d</> files to delete.', count($filesToDelete)));

        if (count($filesToDelete) === 0) {
            $this->info('No files to delete.');

            return 0;
        }

        // Ensure the user wants to delete the files (disallow --no-interaction)
        if (! $this->confirm('Are you sure you want to delete these files?')) {
            $this->warn('Aborting.');

            return 0;
        }

        // K thx bye
        $this->line('Deleting files...');
        Storage::disk($imageService->getStorageDiskName())->delete($filesToDelete);

        return 0;
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            $input->setArgument('model', 'all');

            return;
        }

        $this->interactForModel($input);
    }
}
