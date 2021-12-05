<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Gumbo;

use App\Helpers\Str;
use App\Models\MediaLibrary\LocalPathGenerator;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class MigrateMediaLibraryCommandTest extends TestCase
{
    use WithFaker;

    private const LOCAL_DISK = 'local';

    private const CLOUD_DISK = 'public';

    /**
     * @before
     */
    public function fakeStoragesBeforeTest(): void
    {
        $this->afterApplicationCreated(function () {
            Config::set([
                'filesystems.default' => self::LOCAL_DISK,
                'filesystems.cloud' => self::CLOUD_DISK,
                'medialibrary.disk_name' => self::LOCAL_DISK,
            ]);
        });
    }

    /**
     * Test the base loop.
     */
    public function test_empty(): void
    {
        // Required since the target folder doesn't exist by default
        $filePath = $this->getDummyFilePath();
        Storage::disk(self::LOCAL_DISK)->delete($filePath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);
    }

    /**
     * Test the happy street.
     */
    public function test_with_local_only(): void
    {
        $filePath = $this->getDummyFilePath();

        $localDisk = Storage::disk(self::LOCAL_DISK);
        $cloudDisk = Storage::disk(self::CLOUD_DISK);

        $localDisk->assertExists($filePath);
        $cloudDisk->assertMissing($filePath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);

        $localDisk->assertExists($filePath);
        $cloudDisk->assertExists($filePath);
    }

    /**
     * Test the weird street.
     */
    public function test_with_remote_only(): void
    {
        $filePath = $this->getDummyFilePath();

        $localDisk = Storage::disk(self::LOCAL_DISK);
        $cloudDisk = Storage::disk(self::CLOUD_DISK);

        $localDisk->delete($filePath);
        $cloudDisk->put($filePath, 'test');

        $localDisk->assertMissing($filePath);
        $cloudDisk->assertExists($filePath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);

        $localDisk->assertMissing($filePath);
        $cloudDisk->assertExists($filePath);
    }

    /**
     * Test the already-known street.
     */
    public function test_with_both_existing(): void
    {
        $filePath = $this->getDummyFilePath();

        $localDisk = Storage::disk(self::LOCAL_DISK);
        $cloudDisk = Storage::disk(self::CLOUD_DISK);

        $cloudDisk->put($filePath, 'test');

        $localDisk->assertExists($filePath);
        $cloudDisk->assertExists($filePath);

        $this->artisan('gumbo:migrate-media-library')
            ->assertExitCode(0);

        $localDisk->assertExists($filePath);
        $cloudDisk->assertExists($filePath);
    }

    /**
     * Returns the path of an existing file.
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function getDummyFilePath(): string
    {
        $disk = Config::get('medialibrary.disk_name');

        $path = Str::finish(LocalPathGenerator::BASE_PATH, '/') . "{$this->faker->md5}/{$this->faker->slug}.pdf";

        Storage::disk($disk)->put($path, $this->faker->sentence);

        return $path;
    }
}
