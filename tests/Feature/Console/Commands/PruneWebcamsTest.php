<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use App\Models\Webcam\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneWebcamsTest extends TestCase
{
    private ?User $deviceOwner = null;

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
        $imageDiskName = Config::get('gumbo.images.disk');
        Storage::fake($imageDiskName);

        $imageDisk = Storage::disk($imageDiskName);

        $oldDevices = $this->createDevices(10, [
            'updated_at' => Date::now()->subWeek(),
        ]);

        $currentDevices = $this->createDevices(10, [
            'updated_at' => Date::now(),
        ]);

        $this->artisan('gumbo:prune-webcams');

        $oldDevices->each(fn ($device) => $imageDisk->assertMissing($device->path));
        $currentDevices->each(fn ($device) => $imageDisk->assertExists($device->path));
    }

    public function test_high_device_count_protection(): void
    {
        $imageDiskName = Config::get('gumbo.images.disk');
        Storage::fake($imageDiskName);

        $imageDisk = Storage::disk($imageDiskName);

        $devices = $this->createDevices(30, [
            'updated_at' => Date::now()->subWeek(),
        ]);

        $this->artisan('gumbo:prune-webcams')
            ->assertExitCode(Command::FAILURE);

        $devices->each(fn ($device) => $imageDisk->assertExists($device->path));

        $this->artisan('gumbo:prune-webcams', ['--force' => true])
            ->assertExitCode(Command::SUCCESS);

        $devices->each(fn ($device) => $imageDisk->assertMissing($device->path));
    }

    private function createDevices(int $count, array $props = []): Collection
    {
        $this->deviceOwner ??= $this->getTemporaryUser();

        return Device::factory()
            ->times($count)
            ->for($this->deviceOwner, 'owner')
            ->withImage()
            ->create($props);
    }
}
