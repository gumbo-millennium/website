<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Images;

use App\Console\Commands\Gumbo\Images\Traits\UsesModelsWithImages;
use App\Events\Images\ImageCreated;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshImages extends Command
{
    use UsesModelsWithImages;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    gumbo:images:refresh
        {model : Model class to refresh}
        {--all : Refresh all models, instead of just one}
        {--sync : Refresh all models synchronously}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks all files and re-creates the ones that are missing.';

    /**
     * Image service used to determine the image location.
     */
    protected ImageService $imageService;

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
        $validModelAttributes = [];
        foreach ($models as $modelArg) {
            $this->runOnAllModelImages($modelArg, fn ($model, $attribute, $value) => $validModelAttributes[] = [
                'path' => $imageService->getStoragePathForModelAttribute($model, $value),
                'class' => $model::class,
                'key' => $model->getKey(),
                'attribute' => $attribute,
            ]);
        }
        $this->line(sprintf('Found <info>%d</> properties.', $validModelAttributes));

        // For all folders, create an entry for each size
        $expectedFiles = [];
        $expectationModelMapping = [];
        foreach ($validModelAttributes as $key => $set) {
            foreach ($imageService->getImageSizes() as $size) {
                $sizeSetName = "{$set['path']}/{$size}.webp";
                $expectedFiles[] = $sizeSetName;
                $expectationModelMapping[$sizeSetName] = $key;
            }
        }
        $this->line(sprintf('Expanded to <info>%d</> files to expect.', $expectedFiles));

        // Loads all files from the storage
        $allFiles = Storage::disk($imageService->getStorageDiskName())->allFiles($imageService->getStorageBasePath());
        $this->line(sprintf('Found <info>%d</> existing files.', count($allFiles)));

        // Diff the files
        $missingFiles = array_diff($expectedFiles, $allFiles);
        $this->line(sprintf('Found <info>%d</> files to create.', count($missingFiles)));

        if (count($missingFiles) === 0) {
            $this->info('All models up-to-date.');

            return 0;
        }

        $modelsToCreate = array_map(fn ($file) => $validModelAttributes[$expectationModelMapping[$file]], $missingFiles);
        $modelsToCreateUnique = array_unique($modelsToCreate);

        // Start creating the models
        $this->line("Starting refresh of <info>{$modelsToCreateUnique}</> images...");

        $command = $this->option('sync') ? 'dispatchSync' : 'dispatch';
        $this->withProgressBar(
            $modelsToCreateUnique,
            fn ($job) => ImageCreated::$command($job['class']::find($job['key']), $job['attribute'], $job['value']),
        );

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
