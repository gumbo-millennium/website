<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NewsItem;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests the following flow.
 *
 * - Loading the news page
 * - Creating a new news item and checking if it's shown
 * - Reading our generated news item
 * - Deleting our news item and checking if the page sends a 404
 * - Ensuring the index doesn't have our item anymore
 */
class NewsItemTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        // Get news index
        $response = $this->get(route('news.index'));

        // Ensure it loads
        $response->assertOk();
    }

    /**
     * Tests if the newly created news item is shown.
     *
     * @depends test_index
     */
    public function test_index_with_item(): NewsItem
    {
        // Create random item with a unique title
        $item = factory(NewsItem::class, 1)->create([
            'title' => Str::title(sprintf('Help, op %s kreeg ik tieten', now()->toIso8601String())),
        ])->first();

        // Get news index
        $response = $this->get(route('news.index'));

        // Ensure it loads
        $response->assertOk();

        // Check if we have our article
        $response->assertSeeText($item->title);

        // Return item
        return $item;
    }

    /**
     * Tests if the item can be seen.
     *
     * @depends test_index_with_item
     */
    public function test_view_item(NewsItem $item): void
    {
        // Get news index
        $response = $this->get(route('news.show', ['news' => $item]));

        // Ensure it loads
        $response->assertOk();

        // Check if we see our article's title
        $response->assertSeeText($item->title);
    }

    /**
     * Tests if an item that's deleted, returns a 404.
     *
     * @depends test_index_with_item
     * @depends test_view_item
     */
    public function test_view_deleted_item(NewsItem $item): void
    {
        // Delete item
        $item->delete();

        // Get news index
        $response = $this->get(route('news.show', ['news' => $item]));

        // Ensure it loads
        $response->assertNotFound();
    }

    /**
     * Tests if an item that's deleted, isn't shown on the cover (of Vogue).
     *
     * @depends test_index_with_item
     * @depends test_view_item
     */
    public function test_index_without_deleted_item(NewsItem $item): void
    {
        // Get news index
        $response = $this->get(route('news.index'));

        // Ensure it loads
        $response->assertOk();

        // Check if we cannot see our article
        $response->assertDontSeeText($item->title);
    }
}
