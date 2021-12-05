<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Files\Traits;

use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\Media;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

trait CreatesFiles
{
    use WithFaker;

    /**
     * @before
     */
    public function disableImageProcessing(): void
    {
        $this->afterApplicationCreated(fn () => Config::set([
            'medialibrary.image_generators' => [],
            'medialibrary.image_optimizers' => [],
        ], []));
    }

    /**
     * @before
     */
    public function swapFilesystems(): void
    {
        $this->afterApplicationCreated(function () {
            $disks = array_keys(Config::get('filesystems.disks'));

            foreach ($disks as $diskName) {
                Storage::fake($diskName);
            }
        });
    }

    protected function createFileCategory(): FileCategory
    {
        $category = factory(FileCategory::class)->make([
            'title' => sprintf(
                '[test] %s (%s)',
                $this->faker->sentence,
                Date::now()->format('d-m-Y H:i:s'),
            ),
        ]);

        $category->save();

        return $category;
    }

    protected function createFileBundle(
        ?FileCategory $fileCategory = null,
        bool $published = true
    ): FileBundle {
        $fileCategory ??= $this->createFileCategory();

        /** @var FileBundle $bundle */
        $bundle = factory(FileBundle::class)->make([
            'published_at' => Date::now()->addWeek($published ? -4 : 4),
            'title' => sprintf(
                '[test] %s (%s)',
                $this->faker->sentence,
                Date::now()->format('d-m-Y H:i:s'),
            ),
        ]);

        $bundle->category()->associate($fileCategory);

        $bundle->save();

        return $bundle;
    }

    protected function createFile(
        ?FileBundle $fileBundle = null,
        bool $published = true
    ): Media {
        /** @var FileBundle $bundle */
        $fileBundle ??= $this->createFileBundle();

        $targetFile = new File(resource_path('test-assets/images/hike.jpg'));

        return $fileBundle->addMedia($targetFile)
            ->preservingOriginal()
            ->toMediaCollection();
    }
}
