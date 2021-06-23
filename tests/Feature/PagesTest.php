<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\Str;
use App\Models\Page;
use Tests\TestCase;

class PagesTest extends TestCase
{
    /**
     * @before
     */
    public function updateContentFromGit(): void
    {
        $this->afterApplicationCreated(fn () => $this->artisan('gumbo:update-content'));
    }

    /**
     * Test the homepage.
     *
     * @dataProvider provideTestUrls
     */
    public function test_url(?string $url, int $code): void
    {
        if ($url === null) {
            $this->markTestSkipped('Missing URL');
        }

        // Test response
        $response = $this->get($url);
        $response->assertStatus($code);

        // Test cached response
        $response = $this->get($url);
        $response->assertStatus($code);
    }

    /**
     * Test a random page.
     */
    public function test_seeded_page(): void
    {
        $page = factory(Page::class)->create();

        $this->get(url("/{$page->slug}"))
            ->assertOk()
            ->assertSee($page->title);
    }

    /**
     * Returns test strings.
     *
     * @return array<string,int>
     * @throws InvalidArgumentException
     */
    public function provideTestUrls()
    {
        // Build set
        return [
            'homepage' => ['/', 200],
            'privacy-policy' => ['/privacy-policy', 200],
            'not-found' => [sprintf('/url%s', Str::uuid()), 404],
        ];
    }
}
