<?php

declare(strict_types=1);

namespace App\Console\Commands\Gumbo\Images\Traits;

use App\Fluent\CachedImage;
use App\Models\Traits\HasImages;
use Closure;
use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

trait UsesModelsWithImages
{
    private static ?array $foundImageModels = null;

    /**
     * Returns a list of all available models that have an image trait.
     */
    protected function availableImageModels(): array
    {
        return self::$foundImageModels ??= [...$this->findAvailableImageModels()];
    }

    /**
     * Interacts with the user to get the model to act on, if none is set.
     */
    protected function interactForModel(InputInterface $input): void
    {
        $modelNameClassMap = Collection::make($this->availableImageModels())
            ->mapWithKeys(fn ($class) => [Str::title(class_basename($class)) => $class]);

        if ($modelNameClassMap->isEmpty()) {
            $this->warn('Failed to find any model that might contain images!');
        }

        $chosenClass = $input->getArgument('model');
        if (empty($chosenClass)) {
            $this->line('You have not yet specified a model to prune.');
            $chosenClass = $this->askWithCompletion('Which model would you like to prune?', $modelNameClassMap->keys());
        }

        if ($modelNameClassMap->has($chosenClass)) {
            $chosenClass = $modelNameClassMap->get($chosenClass);
        }

        if($chosenClass) {
            $input->setArgument('model', $chosenClass);
        }
    }

    protected function runOnAllModelImages(string $model, Closure $callback): void
    {
        if (! class_uses_recursive($model, HasImages::class)) {
            throw new InvalidArgumentException("Model {$model} does not use the HasImages trait.");
        }

        $imageAttributes = (new $model())->getImageProperties();

        foreach ($model::query()->withoutGlobalScopes()->lazy(50) as $model) {
            foreach ($imageAttributes as $attribute) {
                $attributeValue = $model->{$attribute};
                if ($attributeValue instanceof CachedImage) {
                    $callback($model, $attribute, $attributeValue);
                }
            }
        }
    }

    /**
     * Finds all models in app/Models that have an image trait.
     */
    private function findAvailableImageModels(): Generator
    {
        $modelsDir = app_path('Models');
        $models = (new Filesystem())->allFiles($modelsDir);

        foreach ($models as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $expectedClassName = (string) Str::of($file->getPathname())
                ->after($modelsDir)
                ->beforeLast('.php')
                ->trim('/')
                ->replace('/', '\\')
                ->prepend('App\\Models\\');

            if (! class_exists($expectedClassName) || ! is_a($expectedClassName, Model::class, true)) {
                continue;
            }

            if (! in_array(HasImages::class, class_uses_recursive($expectedClassName), true)) {
                continue;
            }

            yield $expectedClassName;
        }
    }
}
