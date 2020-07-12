<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\Media as File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests the following cases
 *
 * As anonymous:
 * - all file routes (302 to login)
 *
 * As non-member user:
 * - all file routes (403)
 *
 * As member;
 * - File category list (200)
 * - Category detail on existing category (200)
 * - Category detail on non-existing category (404)
 * - File detail on existing file (200)
 * - File detail on non-existing file (404)
 * - Download on existing file as bundle (200)
 * - Download on existing file as single (200)
 * - Download on existing file with missing attachment (404)
 * - Download on non-existing file (404)
 */
class FileDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected const PDF_FILE = 'test-assets/pdf/chicken.pdf';

    /**
     * @var \App\Models\FileCategory
     */
    protected static ?FileCategory $category = null;

    /**
     * @var \App\Models\FileBundle
     */
    protected static ?FileBundle $bundle = null;

    /**
     * Creates a random category
     * @return FileCategory
     */
    protected function getRandomCategory(): FileCategory
    {
        return \factory(FileCategory::class, 1)->create()->first();
    }

    /**
     * Creates a random bundle (in the given $category)
     * @param null|FileCategory $category
     * @return FileBundle
     */
    protected function getRandomBundle(?FileCategory $category = null): FileBundle
    {
        $category ??= $this->getRandomCategory();
        return \factory(FileBundle::class, 1)
            ->create(['category_id' => $category->id])
            ->first();
    }

    /**
     * Creates a randomly generated file (in the given $bundle)
     * @param null|FileBundle $bundle
     * @return File
     */
    protected function getRandomFile(?FileBundle $bundle = null): File
    {
        // Get or create bundle
        $bundle ??= $this->getRandomBundle();

        // Add media if none present
        if ($bundle->media->isEmpty()) {
            // Add file
            $bundle
                ->addMedia(new HttpFile(\resource_path(self::PDF_FILE)))
                ->preservingOriginal()
                ->toMediaCollection();

            // Reload model
            $bundle->refresh();
        }

        // Return first media item
        return $bundle->media->first();
    }

    /**
     * Test viewing as logged out user
     * @param string $route
     * @return void
     */
    public function testViewAsAnonymous()
    {
        // Test all routes
        foreach ($this->getTestRoutes() as $route) {
            // Request the file
            $response = $this->get($route);

            // Expect to be redirected to the login
            $response->assertRedirect(URL::route('login'));
        }
    }

    /**
     * A basic feature test example.
     * @param string $route
     * @return void
     */
    public function testViewAsGuest()
    {
        // Get a guest user
        $user = $this->getGuestUser();

        // Test all routes
        foreach ($this->getTestRoutes() as $route) {
            // Request the file
            $response = $this->actingAs($user)->get($route);

            // Expect to be shown a 403
            $response->assertForbidden();
        }
    }

    /**
     * Test if we're seeing our first category when looking at the file index
     * @return void
     */
    public function testViewIndex()
    {
        // Get local models
        $category = $this->getRandomCategory();
        $category2 = $this->getRandomCategory();
        $category3 = $this->getRandomCategory();
        $bundle = $this->getRandomBundle($category);
        $bundle2 = $this->getRandomBundle($category);
        $bundle3 = $this->getRandomBundle($category);
        $bundle4 = $this->getRandomBundle($category2);

        // Get routes and member
        $routes = $this->getTestRoutes($category, $bundle);
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)->get($routes['index']);

        // Expect an OK response
        $response->assertOk();

        // Check contents
        $response->assertSeeText($category->title);
        $response->assertSeeText($bundle->title);
        $response->assertSeeText($bundle2->title);
        $response->assertSeeText($bundle3->title);

        // Test 2nd category
        $response->assertSeeText($category2->title);
        $response->assertSeeText($bundle4->title);

        // Test empty category
        $response->assertDontSeeText($category3->title);
    }

    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     * @return void
     */
    public function testViewExistingCategory()
    {
        // Get local models
        $category = $this->getRandomCategory();
        $bundle = $this->getRandomBundle($category);
        $bundle2 = $this->getRandomBundle($category);
        $bundle3 = $this->getRandomBundle();

        // Get routes and member
        $routes = $this->getTestRoutes($category, $bundle);
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)
            ->get($routes['category']);

        // Expect an OK response
        $response->assertOk();

        // Check if the bundle title is present
        $response->assertSeeText($category->title);
        $response->assertSeeText($bundle->title);
        $response->assertSeeText($bundle2->title);
        $response->assertDontSeeText($bundle3->title);
    }

    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     * @return void
     */
    public function testViewBundle()
    {
        // Get local models
        $bundle = $this->getRandomBundle();
        $category = $bundle->category;

        // Get routes and member
        $routes = $this->getTestRoutes($category, $bundle);
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)
            ->get($routes['show']);

        // Expect an OK response
        $response->assertOk();

        // Check if the bundle title is present
        $response->assertSeeText($category->title);
        $response->assertSeeText($bundle->title);
    }

    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     * @return void
     */
    public function testDownloadBundle()
    {
        // Get local models
        $bundle = $this->getRandomBundle();

        // Get routes and member
        $routes = $this->getTestRoutes($bundle->category, $bundle);
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)
            ->get($routes['download']);

        // Expect an OK response
        $response->assertOk();

        // Expect an OK response
        $response->assertOk();
        $response->assertHeader('Content-Disposition', "attachment; filename=\"{$bundle->title}.zip\"");
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $response->assertHeaderMissing('Location');
    }


    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     * @return void
     */
    public function testDownloadBundleFile()
    {
        // Get local models
        $file = $this->getRandomFile();

        // Get routes and member
        $routes = $this->getTestRoutes(null, null, $file);
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)
            ->get($routes['download-single']);

        // Expect an OK response
        $response->assertOk();

        // Expect an OK response
        $response->assertOk();
        $response->assertHeader('Content-Disposition', "attachment; filename={$file->file_name}");
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeaderMissing('Location');
    }

    /**
     * Test if we're getting a 404 when requesting a non-existing category
     * @return void
     */
    public function testViewNonExistingCategory()
    {
        $routes = $this->getTestRoutes();
        if (!array_key_exists('category-missing', $routes)) {
            $this->markTestIncomplete('Cannot find [category-missing] key in route list');
        }

        // Get a guest user
        $user = $this->getMemberUser();

        // Request the index
        $response = $this->actingAs($user)
            ->get($routes['category-missing']);

        // Expect an OK response
        $response->assertNotFound();
    }

    /**
     * Provides a list of routes
     * @param null|FileCategory $category
     * @param null|FileBundle $bundle
     * @param null|File $file
     * @return array<string>
     */
    protected function getTestRoutes(
        ?FileCategory $category = null,
        ?FileBundle $bundle = null,
        ?File $file = null
    ): array {
        // Get proper values
        $category ??= $this->getRandomCategory();
        $bundle ??= $this->getRandomBundle($category);
        $file ??= $this->getRandomFile($bundle);

        // Build routes
        return [
            'index' => URL::route('files.index'),

            'category' => URL::route('files.category', ['category' => $category]),
            'category-missing' => URL::route('files.category', ['category' => (string) Str::uuid()]),

            'show' => URL::route('files.show', ['bundle' => $bundle]),
            'show-missing' => URL::route('files.show', ['bundle' => (string) Str::uuid()]),

            'download' => URL::route('files.download', ['bundle' => $bundle]),
            'download-missing' => URL::route('files.download', ['bundle' => (string) Str::uuid()]),

            'download-single' => URL::route('files.download-single', ['media' => $file]),
            'download-single-missing' => URL::route('files.download-single', ['media' => (string) Str::uuid()])
        ];
    }

    /**
     * Returns list of routes with missing bool as 2nd value
     * @param null|FileCategory $category
     * @param null|FileBundle $bundle
     * @param null|File $file
     * @return array<array<string,bool>>
     */
    protected function getTestRoutesWithMissingFlag(
        ?FileCategory $category = null,
        ?FileBundle $bundle = null,
        ?File $file = null
    ): array {
        // Map routes with a 'missing' flag
        return collect($this->getTestRoutes($category, $bundle, $file))
            ->map(static fn ($value, $key) => [$value, Str::endsWith($key, '-missing') === false])
            ->toArray();
    }
}
