<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Webcam\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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

    public function test_default_pruning(): void
    {
        $imageDisk = Config::get('gumbo.images.disk');
        Storage::fake($imageDisk);

        $oldDevices = $this->createDevices(10, [
            'updated_at' => Date::now()->subWeek(),
        ]);

        $currentDevices = $this->createDevices(10, [
            'updated_at' => Date::now(),
        ]);

        $this->artisan('gumbo:prune-webcams');

        $oldDevices->each(fn ($device) => Storage::assertMissing($device->path));
        $currentDevices->each(fn ($device) => Storage::assertExists($device->path));
    }

    public function test_high_number_pruning(): void
    {
        $imageDisk = Config::get('gumbo.images.disk');
        Storage::fake($imageDisk);

        $devices = $this->createDevices(30, [
            'updated_at' => Date::now()->subWeek(),
        ]);

        $this->artisan('gumbo:prune-webcams');

        $devices->each(fn ($device) => Storage::assertExists($device->path));

        $this->artisan('gumbo:prune-webcams', ['--force' => true]);

        $devices->each(fn ($device) => Storage::assertMissing($device->path));
    }

    private function createDevices(int $count, array $props = []): Collection
    {
        return Device::withImages()->times($count)->create($props);
    }
}
