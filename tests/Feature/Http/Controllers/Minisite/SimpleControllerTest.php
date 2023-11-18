<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Minisite;

use App\Models\Page;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SimpleControllerTest extends TestCase
{
    /**
     * Ensure some pages exist on the site.
     * @before
     */
    public function setupSitePages(): void
    {
        $this->afterApplicationCreated(function () {
            Config::set('gumbo.minisites', [
                'test-site.localhost' => [
                    'enabled' => true,
                ],
            ]);

            Page::factory()->createMany([
                [
                    'slug' => 'home',
                    'title' => 'Test Site Homepage Localhost',
                    'group' => 'test-site.localhost',
                ],
                [
                    'slug' => 'about',
                    'title' => 'Test Site AboutPage Localhost',
                    'group' => 'test-site.localhost',
                ],
            ]);
        });
    }

    public function test_default_behaviour(): void
    {
        $this->get('http://test-site.localhost')
            ->assertOk();

        $this->get('http://test-site.localhost/about')
            ->assertOk();

        $this->get('http://test-site.localhost/does-not-exist')
            ->assertNotFound();
    }

    public function test_home_route_redirects_home(): void
    {
        $this->get('http://test-site.localhost/home')
            ->assertRedirect('http://test-site.localhost');
    }

    public function test_csp_headers_contain_the_mix_url(): void
    {
        Config::set([
            'app.url' => 'http://example.net',
            'app.mix_url' => 'http://example.com/assets',
        ]);

        $cspHeader = $this->get('http://test-site.localhost')
            ->assertOk()
            ->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('http://example.net', $cspHeader);
        $this->assertStringContainsString('http://example.com', $cspHeader);
    }
}
