<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Files;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Http\Controllers\Files\Traits\CreatesFiles;
use Tests\TestCase;

class FileDisplayTest extends TestCase
{
    use CreatesFiles;
    use WithFaker;

    public function test_viewing_any_file_route_as_anon_redirects_to_login(): void
    {
        $this->fetchRoutes(
            null,
            fn (TestResponse $response) => $response->assertRedirect(route('login')),
        );
    }

    public function test_non_member_users_cannot_view_any_route(): void
    {
        $this->fetchRoutes(
            $this->getGuestUser(),
            fn (TestResponse $response) => $response->assertForbidden(),
        );
    }

    public function test_members_can_view_any_route(): void
    {
        $this->fetchRoutes(
            $this->getMemberUser(),
            fn (TestResponse $response) => $response->assertOk(),
        );
    }

    public function test_index_view(): void
    {
        $expectedCategories = [
            $this->createFileCategory(),
            $this->createFileCategory(),
            $this->createFileCategory(),
        ];
        $unexpectedCategories = [
            $this->createFileCategory(),
            $this->createFileCategory(),
        ];

        foreach ($expectedCategories as $category) {
            for ($i = $this->faker->numberBetween(1, 3); $i > 0; $i--) {
                $bundle = $this->createFileBundle($category);
                $this->createFile($bundle);
            }
        }

        // Get page
        $result = $this
            ->actingAs($this->getMemberUser())
            ->get(route('files.index'))
            ->assertOk();

        // Expect to see categories with bundles
        foreach ($expectedCategories as $category) {
            $result->assertSeeText($category->title);
        }

        // Expect categories without bundles to be hidden
        foreach ($unexpectedCategories as $category) {
            $result->assertDontSeeText($category->title);
        }
    }

    public function test_view_existing_category(): void
    {
        $this->actingAs($this->getMemberUser());

        $category = $this->createFileCategory();
        $filledBundle = $this->createFileBundle($category);
        $file = $this->createFile($filledBundle);

        $emptyBundle = $this->createFileBundle($category);

        $hiddenBundle = $this->createFileBundle($category, false);

        // Get existing and published
        $this
            ->get(route('files.category', $category))
            ->assertOk()
            ->assertSeeText($category->title)
            ->assertSeeText($filledBundle->title)
            ->assertSeeText($emptyBundle->title)
            ->assertDontSeeText($hiddenBundle->title);

        // Get non-existing
        $this
            ->get(route('files.category', (string) Str::uuid()))
            ->assertNotFound();
    }

    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     */
    public function test_view_bundle(): void
    {
        $this->actingAs($this->getMemberUser());

        $category = $this->createFileCategory();
        $filledBundle = $this->createFileBundle($category);
        $file = $this->createFile($filledBundle);

        $emptyBundle = $this->createFileBundle($category);

        $hiddenBundle = $this->createFileBundle($category, false);

        // Get filled and published
        $this
            ->get(route('files.show', $filledBundle))
            ->assertOk()
            ->assertSeeText($category->title)
            ->assertSeeText($filledBundle->title)
            ->assertSee($file->name);

        // Get empty and published
        $this
            ->get(route('files.show', $emptyBundle))
            ->assertOk()
            ->assertSeeText($category->title)
            ->assertSeeText($emptyBundle->title);

        // Get not published
        $this
            ->get(route('files.show', $hiddenBundle))
            ->assertNotFound();

        // Get non-existing
        $this
            ->get(route('files.show', (string) Str::uuid()))
            ->assertNotFound();
    }

    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     */
    public function test_download_bundle(): void
    {
        $this->actingAs($this->getMemberUser());

        $category = $this->createFileCategory();
        $filledBundle = $this->createFileBundle($category);
        $file = $this->createFile($filledBundle);

        $emptyBundle = $this->createFileBundle($category);

        $hiddenBundle = $this->createFileBundle($category, false);

        // Get filled and published
        $this
            ->get(route('files.download', $filledBundle))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=\"{$filledBundle->title}.zip\"")
            ->assertHeader('Content-Type', 'application/octet-stream')
            ->assertHeaderMissing('Location');

        // Get empty and published
        $this
            ->get(route('files.download', $emptyBundle))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=\"{$emptyBundle->title}.zip\"")
            ->assertHeader('Content-Type', 'application/octet-stream')
            ->assertHeaderMissing('Location');

        // Get unpublished
        $this
            ->get(route('files.download', $hiddenBundle))
            ->assertNotFound();
    }

    /**
     * Test if we're seeing the right bundles when looking at an existing category.
     */
    public function test_download_bundle_file(): void
    {
        $this->actingAs($this->getMemberUser());

        $category = $this->createFileCategory();
        $filledBundle = $this->createFileBundle($category);
        $filledFile = $this->createFile($filledBundle);

        $hiddenBundle = $this->createFileBundle($category, false);
        $hiddenFile = $this->createFile($hiddenBundle);

        // Get filled and published
        $this
            ->get(route('files.download-single', $filledFile))
            ->assertOk()
            ->assertHeader('Content-Type', $filledFile->mime_type)
            ->assertHeaderMissing('Location');

        // Get unpublished
        $this
            ->get(route('files.download-single', $hiddenFile))
            ->assertNotFound();
    }

    private function fetchRoutes(?User $actingAs, Closure $check): void
    {
        if ($actingAs) {
            $this->actingAs($actingAs);
        }

        $category = $this->createFileCategory();
        $bundle = $this->createFileBundle($category);
        $file = $this->createFile($bundle);

        // Request the index
        $check(
            $this->get(route('files.index'))
                ->assertDontSee('Internal Server Error', 'Request to files.index failed'),
        );

        // Request the category
        $check(
            $this->get(route('files.category', $category))
                ->assertDontSee('Internal Server Error', 'Request to files.category failed'),
        );

        // Request the file
        $check(
            $this->get(route('files.show', $bundle))
                ->assertDontSee('Internal Server Error', 'Request to files.show failed'),
        );

        // Download the bundle
        $check(
            $this->get(route('files.download', $bundle))
                ->assertDontSee('Internal Server Error', 'Request to files.download failed'),
        );

        // Download the file
        $check(
            $this->get(route('files.download-single', $file))
                ->assertDontSee('Internal Server Error', 'Request to files.download-single failed'),
        );

        // Search for something
        $check(
            $this->get(route('files.search', ['query' => $this->faker->word]))
                ->assertDontSee('Internal Server Error', 'Request to files.search failed'),
        );
    }
}
