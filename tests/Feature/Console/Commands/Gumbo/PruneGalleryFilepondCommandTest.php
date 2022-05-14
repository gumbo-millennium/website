<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Gumbo;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneGalleryFilepondCommandTest extends TestCase
{
    /**
     * Tests that cleaning only removes files in the proper locations.
     */
    public function test_pruning_works_as_expected(): void
    {
        $disk = Config::get('gumbo.gallery.filepond.disk');
        $path = Config::get('gumbo.gallery.filepond.path');

        Storage::fake($disk);

        $this->markTestIncomplete('Need to finish this');

        Storage::disk($disk)->put('/test.jpg', 'test');
        Storage::disk($disk)->put("${path}/test.jpg", 'test');

        $this->travel(6)->weeks();

        Storage::disk($disk)->put("${path}/test2.jpg", 'test');

        $this->artisan('gumbo:prune-glide-cache')
            ->assertSuccessful();

        Storage::disk($disk)->assertExists('/test.jpg');
        Storage::disk($disk)->assertExists("${path}/test2.jpg");
        Storage::disk($disk)->assertMissing("${path}/test.jpg");
    }

    /**
     * Test cleaning doesn't touch anything outside of the filepond paths.
     */
    public function test_clean_command_scopes_properly(): void
    {
        $disk = Config::get('gumbo.gallery.filepond.disk');
        $path = Config::get('gumbo.gallery.filepond.path');

        Storage::fake($disk);

        $existingRoot = 'test.jpg';
        $existingParent = dirname($path) . '/test.jpg';
        $targetNode = $path . '/test.jpg';

        Storage::disk($disk)->put($existingRoot, 'test');
        Storage::disk($disk)->put($existingParent, 'test');
        Storage::disk($disk)->put($targetNode, 'test');

        $this->travel(6)->weeks();

        $this->artisan('gumbo:prune-gallery-filepond', ['--clean' => true])
            ->assertSuccessful();

        Storage::disk($disk)->assertExists($existingRoot);
        Storage::disk($disk)->assertExists($existingParent);
        Storage::disk($disk)->assertMissing($targetNode);
    }
}
