<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Gumbo;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneGlideCacheCommandTest extends TestCase
{
    /**
     * Tests that cleaning only removes files in the proper locations.
     */
    public function test_cleaning_scopes_are_proper(): void
    {
        $disk = Config::get('gumbo.glide.cache-disk');
        $path = Config::get('gumbo.glide.cache-path');

        Storage::fake($disk);

        Storage::disk($disk)->put('/test.jpg', 'test');
        Storage::disk($disk)->put("{$path}/test.jpg", 'test');

        $this->travel(6)->weeks();

        $this->artisan('gumbo:prune-glide-cache', ['--force' => true])
            ->assertSuccessful();

        Storage::disk($disk)->assertExists('/test.jpg');
        Storage::disk($disk)->assertMissing("{$path}/test.jpg");
    }
}
