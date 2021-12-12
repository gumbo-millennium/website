<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Webcam;
use App\Models\WebcamUpdate;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneWebcamUpdatesTest extends TestCase
{
    /**
     * @before
     */
    public function fakeStorageBeforeTest(): void
    {
        $this->afterApplicationCreated(function () {
            Storage::fake();
        });
    }

    public function test_pruning(): void
    {
        [$cam] = $this->createTestWebcam(30);

        $this->artisan('gumbo:prune-webcams');

        $this->assertCount(20, $cam->refresh()->updates);
    }

    public function test_pruning_custom_count(): void
    {
        /** @var Webcam $cam */
        [$cam, $models] = $this->createTestWebcam(30);

        $this->artisan('gumbo:prune-webcams', ['--keep' => 15]);

        $sortedModels = $models->sortByDesc('created_at');

        $this->assertCount(15, $cam->refresh()->updates);

        $this->assertDatabaseHas('webcam_updates', ['id' => $sortedModels->first()->id]);
        $this->assertDatabaseMissing('webcam_updates', ['id' => $sortedModels->last()->id]);
    }

    public function test_pruning_in_order(): void
    {
        /** @var Webcam $cam */
        [$cam] = $this->createTestWebcam(0);

        $cam->updates()->saveMany([
            $old = WebcamUpdate::factory()->make(['created_at' => Date::now()->subMonths(4)]),
            $new = WebcamUpdate::factory()->make(['created_at' => Date::now()]),
        ]);

        $this->assertTrue($old->exists);
        $this->assertTrue($new->exists);

        $this->assertCount(2, $cam->refresh()->updates);

        $this->artisan('gumbo:prune-webcams', ['--keep' => 1]);

        $this->assertNull(WebcamUpdate::find($old->id));
        $this->assertNotNull(WebcamUpdate::find($new->id));

        $this->assertCount(1, $cam->refresh()->updates);
    }

    public function test_pruning_oprhans(): void
    {
        /** @var Webcam $cam */
        [$cam] = $this->createTestWebcam(0);

        $factory = WebcamUpdate::factory()->withImage();

        $fourMonthOld = $factory->make(['created_at' => Date::now()->subMonths(4)]);
        $twoMonthOld = $factory->make(['created_at' => Date::now()->subMonths(2)]);
        $oneMonthOld = $factory->make(['created_at' => Date::now()->subMonth()]);
        $oneHourOld = $factory->make(['created_at' => Date::now()->subHour()]);

        $cam->updates()->saveMany([$fourMonthOld, $twoMonthOld, $oneMonthOld, $oneHourOld]);

        $this->assertCount(4, $cam->refresh()->updates);

        // Delete two resources without deleting their images
        WebcamUpdate::withoutEvents(fn () => $fourMonthOld->delete());

        // Ensure the four month old update is deleted
        $this->assertNull(WebcamUpdate::find($fourMonthOld->id));

        // Ensure the filesystem has the images
        Storage::assertExists($fourMonthOld->image_path);
        Storage::assertExists($twoMonthOld->image_path);
        Storage::assertExists($oneMonthOld->image_path);
        Storage::assertExists($oneHourOld->image_path);

        // Prune the other two cams too
        $this->artisan('gumbo:prune-webcams', ['--keep' => 2]);

        // Ensure the proper models were removed
        $this->assertNull(WebcamUpdate::find($twoMonthOld->id));
        $this->assertNotNull(WebcamUpdate::find($oneMonthOld->id));
        $this->assertNotNull(WebcamUpdate::find($oneHourOld->id));

        // Ensure the floating images were removed
        Storage::assertMissing($fourMonthOld->image_path);
        Storage::assertMissing($twoMonthOld->image_path);

        // Ensure the last two images is still there
        Storage::assertExists($oneMonthOld->image_path);
        Storage::assertExists($oneHourOld->image_path);

        // Last sanity check
        $this->assertCount(2, $cam->refresh()->updates);
    }

    /**
     * @return array[Webcam,Collection|WebcamUpdate]
     */
    private function createTestWebcam(int $count): array
    {
        $cam = Webcam::factory()->create();

        if ($count === 0) {
            return [$cam, collect()];
        }

        /** @var \Illuminate\Database\Eloquent\Collection $models */
        $models = WebcamUpdate::factory()->times($count)->make();

        $cam->updates()->saveMany($models);

        $this->assertCount($count, $cam->refresh()->updates);

        return [$cam, $models->toBase()];
    }
}
