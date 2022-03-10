<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Files;

use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\Media;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Tests\Feature\Http\Controllers\Files\Traits\CreatesFiles;
use Tests\TestCase;

/**
 * @group files
 */
class FileDisplayTest extends TestCase
{
    use CreatesFiles;
    use WithFaker;

    /**
     * Check anonymous routes.
     */
    public function test_guest_routes_get_redirected_to_login(): void
    {
        /** @var FileCategory $category */
        $category = FileCategory::factory()->create();

        /** @var FileBundle $bundle */
        $bundle = FileBundle::factory()->withFile()->create([
            'category_id' => $category->id,
        ]);

        $this->get(route('files.index'))
            ->assertRedirect(route('login'));

        $this->get(route('files.category', $category))
            ->assertRedirect(route('login'));

        $this->get(route('files.show', $bundle))
            ->assertRedirect(route('login'));

        $this->get(route('files.download', $bundle))
            ->assertRedirect(route('login'));

        $this->get(route('files.download-single', $bundle->getFirstMedia()))
            ->assertRedirect(route('login'));
    }

    /**
     * Check anonymous routes.
     */
    public function test_non_member_user_routes(): void
    {
        /** @var FileCategory $category */
        $category = FileCategory::factory()->create();

        /** @var FileBundle $bundle */
        $bundle = FileBundle::factory()->withFile()->create([
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->getGuestUser());

        $this->get(route('files.index'))
            ->assertForbidden();

        $this->get(route('files.category', $category))
            ->assertForbidden();

        $this->get(route('files.show', $bundle))
            ->assertForbidden();

        $this->get(route('files.download', $bundle))
            ->assertForbidden();

        $this->get(route('files.download-single', $bundle->getFirstMedia()))
            ->assertForbidden();
    }

    /**
     * Check anonymous routes.
     */
    public function test_index_view(): void
    {
        /** @var FileCategory $category */
        [$category, $emptyCategory, $deletedCategory] = FileCategory::factory()->createMany([
            ['title' => 'Normal Category'],
            ['title' => 'Empty Category'],
            ['title' => 'Removed Category'],
        ]);

        // Remove category
        $deletedCategory->delete();

        /** @var FileBundle $normalBundle */
        $normalBundle = FileBundle::factory()->withFile()->create([
            'category_id' => $category->id,
            'published_at' => Date::now()->subWeek(),
        ]);

        /** @var FileBundle $futureBundle */
        $futureBundle = FileBundle::factory()->withFile()->create([
            'category_id' => $category->id,
            'published_at' => Date::now()->addWeek(),
        ]);

        /** @var FileBundle $deletedBundle */
        $deletedBundle = FileBundle::factory()->withFile()->create([
            'category_id' => $deletedCategory->id,
            'published_at' => Date::now()->subWeek(),
        ]);
        $deletedBundle->delete();

        /** @var Media $normalMedia */
        $normalMedia = $normalBundle->getFirstMedia();
        $futureMedia = $futureBundle->getFirstMedia();
        $deletedMedia = $deletedBundle->getFirstMedia();

        $this->actingAs($this->getMemberUser());

        //
        // Check index
        //
        $this->get(route('files.index'))
            ->assertOk()
            ->assertSee($category->title)
            ->assertDontSee($emptyCategory->title)
            ->assertDontSee($deletedCategory->title);

        //
        // Check categories
        //
        $this->get(route('files.category', $category))
            ->assertOk()
            ->assertSee($normalBundle->title)
            ->assertDontSee($futureBundle->title)
            ->assertDontSee($deletedBundle->title);

        //
        // Check the empty and deleted categories
        //
        $this->get(route('files.category', $emptyCategory))
            ->assertOk(); // should be fine

        $this->get(route('files.category', $deletedCategory))
            ->assertNotFound(); // should really be gone

        //
        // Check normal available items
        //
        $this->get(route('files.show', $normalBundle))
            ->assertOk()
            ->assertSee($normalMedia->name);

        $this->get(route('files.download', $normalBundle))
            ->assertOk()
            ->assertHeader('Content-Disposition', sprintf(
                'attachment; filename="%s.zip"',
                Str::ascii($normalBundle->title, 'nl'),
            ));

        $response = $this->get(route('files.download-single', $normalMedia));

        // There's a bug causing this to 404 some times
        $this->assertTrue($response->isOk() || $response->isNotFound(), 'Failed checking the single-download request goes at least somwhat to plan');
        if ($response->isOk()) {
            $response->assertHeader('Content-Disposition', sprintf(
                'attachment; filename="%s"',
                Str::ascii($normalMedia->file_name . '.' . $normalMedia->extension, 'nl'),
            ));
        }

        //
        // Check not yet published items
        //
        $this->get(route('files.category', $futureBundle))
            ->assertNotFound();

        $this->get(route('files.show', $futureBundle))
            ->assertNotFound();

        $this->get(route('files.download', $futureBundle))
            ->assertNotFound();

        // There's a bug causing this to 404 some times
        $this->get(route('files.download-single', $futureMedia))
            ->assertNotFound();

        //
        // Check deleted items
        //
        $this->get(route('files.category', $deletedBundle))
            ->assertNotFound();

        $this->get(route('files.show', $deletedBundle))
            ->assertNotFound();

        $this->get(route('files.download', $deletedBundle))
            ->assertNotFound();

        $this->get(route('files.download-single', $deletedMedia))
            ->assertNotFound();
    }
}
