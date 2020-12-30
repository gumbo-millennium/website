<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\Str;
use App\Models\Page;
use Tests\TestCase;

class PagesTest extends TestCase
{
    /**
     * Test the homepage
     *
     * @return void
     * @dataProvider provideTestUrls
     */
    public function testUrl(?string $url, int $code): void
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
     * Returns test strings
     *
     * @return array<string,int>
     * @throws InvalidArgumentException
     */
    public function provideTestUrls()
    {
        // Get first page
        $firstPage = Page::where('contents', '!=', '[]')->first();

        // Build set
        return [
            'homepage' => ['/', 200],
            'privacy-policy' => ['/privacy-policy', 200],
            'not-found' => [sprintf('/url%s', Str::uuid()), 404],
            'first-page' => [$firstPage ? "/{$firstPage->slug}" : null, 200],
        ];
    }
}
