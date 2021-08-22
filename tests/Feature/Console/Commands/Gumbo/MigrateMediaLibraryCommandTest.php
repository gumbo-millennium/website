<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Gumbo;

use App\Models\FileBundle;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MigrateMediaLibraryCommandTest extends TestCase
{
    private const LOCAL_DISK = 'test-local';

    private const CLOUD_DISK = 'test-cloud';

    /**
     * @before
     */
    public function fakeStoragesBeforeTest(): void
    {
        $this->afterApplicationCreated(function () {
            Config::set('filesystems.disks.' . self::LOCAL_DISK, [
                'driver' => 'local',
                'root' => $localRoot = storage_path('app/testing/local'),
            ]);

            Config::set('filesystems.disks.' . self::CLOUD_DISK, [
                'driver' => 'local',
                'root' => $cloudRoot = storage_path('app/testing/cloud'),
            ]);

            is_dir($localRoot) || mkdir($localRoot, 0777, true);
            is_dir($cloudRoot) || mkdir($cloudRoot, 0777, true);

            Config::set([
                'filesystems.default' => self::LOCAL_DISK,
                'filesystems.cloud' => self::CLOUD_DISK,
                'medialibrary.disk_name' => self::LOCAL_DISK,
            ]);

            Storage::fake('test-local');
            Storage::fake('test-cloud');
        });
    }

    /**
     * Test the base loop.
     */
    public function test_empty()
    {
        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);
    }

    /**
     * Test the happy street.
     */
    public function test_with_local_only(): void
    {
        $bundle = $this->getDummyFileBundle();
        $localPath = $bundle->getFirstMedia()->path;

        $localDisk = Storage::disk(self::LOCAL_DISK);
        $cloudDisk = Storage::disk(self::CLOUD_DISK);

        $localDisk->assertExists($localPath);
        $cloudDisk->assertMissing($localPath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);

        $localDisk->assertExists($localPath);
        $cloudDisk->assertExists($localPath);
    }

    /**
     * Test the weird street.
     */
    public function test_with_remote_only(): void
    {
        $bundle = $this->getDummyFileBundle();
        $localPath = $bundle->getFirstMedia()->path;

        $localDisk = Storage::disk(self::LOCAL_DISK);
        $cloudDisk = Storage::disk(self::CLOUD_DISK);

        $localDisk->delete($localPath);
        $cloudDisk->put($localPath, 'test');

        $localDisk->assertMissing($localPath);
        $cloudDisk->assertExists($localPath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);

        $localDisk->assertMissing($localPath);
        $cloudDisk->assertExists($localPath);
    }

    /**
     * Test the already-known street.
     */
    public function test_with_both_existing(): void
    {
        $bundle = $this->getDummyFileBundle();
        $localPath = $bundle->getFirstMedia()->path;

        $localDisk = Storage::disk(self::LOCAL_DISK);
        $cloudDisk = Storage::disk(self::CLOUD_DISK);

        $cloudDisk->put($localPath, 'test');

        $localDisk->assertExists($localPath);
        $cloudDisk->assertExists($localPath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);

        $localDisk->assertExists($localPath);
        $cloudDisk->assertExists($localPath);
    }

    private function getDummyFileBundle(): FileBundle
    {
        /** @var FileBundle $instance */
        $instance = factory(FileBundle::class)->create();

        $instance->addMedia(resource_path('test-assets/pdf/chicken.pdf'))
            ->preservingOriginal()
            ->withManipulations([])
            ->toMediaCollection();

        $this->assertNotNull($instance->getFirstMedia());

        $localDisk = Storage::disk(self::LOCAL_DISK);

        $localPath = $instance->getFirstMedia()->path;

        if (! $localDisk->exists($localPath)) {
            $localDisk->put($localPath, 'test');
        }

        return FileBundle::query()
            ->with(['media'])
            ->find($instance->id);
    }
}
