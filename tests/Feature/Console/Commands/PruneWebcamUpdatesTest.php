<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Webcam;
use App\Models\WebcamUpdate;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class PruneWebcamUpdatesTest extends TestCase
{
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
            $old = factory(WebcamUpdate::class)->make(['created_at' => Date::now()->subMonths(4)]),
            $new = factory(WebcamUpdate::class)->make(['created_at' => Date::now()]),
        ]);

        $this->assertTrue($old->exists);
        $this->assertTrue($new->exists);

        $this->assertCount(2, $cam->refresh()->updates);

        $this->artisan('gumbo:prune-webcams', ['--keep' => 1]);

        $this->assertNull(WebcamUpdate::find($old->id));
        $this->assertNotNull(WebcamUpdate::find($new->id));

        $this->assertCount(1, $cam->refresh()->updates);
    }

    /**
     * @return array[Webcam,Collection|WebcamUpdate]
     */
    private function createTestWebcam(int $count): array
    {
        $cam = factory(Webcam::class)->create();

        if ($count === 0) {
            return [$cam, collect()];
        }

        /** @var \Illuminate\Database\Eloquent\Collection $models */
        $models = factory(WebcamUpdate::class)->times($count)->make();

        $cam->updates()->saveMany($models);

        $this->assertCount($count, $cam->refresh()->updates);

        return [$cam, $models->toBase()];
    }
}
