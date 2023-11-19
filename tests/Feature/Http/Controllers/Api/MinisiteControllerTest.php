<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Minisite\Site;
use App\Models\Minisite\SitePage as SitePage;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class MinisiteControllerTest extends TestCase
{
    public function test_config_non_existent(): void
    {
        $domain = 'config.missing.example.com';

        $this->getJson(route('api.minisite.config', [$domain]))
            ->assertNotFound();
    }

    public function test_config_found(): void
    {
        $site = Site::factory()->create([
            'domain' => 'config.test.example.com',
            'enabled' => true,
        ]);

        $this->getJson(route('api.minisite.config', [$site->domain]))
            ->assertOk()
            ->assertJsonFragment([
                'domain' => $site->domain,
                'name' => $site->name,
                'enabled' => true,
            ]);
    }

    public function test_sitemap(): void
    {
        Date::setTestNow('2023-01-01 12:00:00');

        $site = Site::factory()
            ->hasPages(3)
            ->create([
                'domain' => 'sitemap.empty.example.com',
                'enabled' => false,
            ]);

        $this->getJson(route('api.minisite.sitemap', [$site->domain]))
            ->assertOk()
            ->assertJsonFragment($site->pages->only('id', 'slug')->all());
    }

    public function test_sitemap_non_existent(): void
    {
        $domain = 'sitemap.missing.example.com';

        $this->getJson(route('api.minisite.sitemap', [$domain]))
            ->assertNotFound();
    }

    public function test_sitemap_disabled(): void
    {
        $site = Site::factory()
            ->hasPages(SitePage::factory(6))
            ->create([
                'domain' => 'sitemap.disabled.example.com',
                'enabled' => false,
            ]);

        $this->getJson(route('api.minisite.sitemap', [$site->domain]))
            ->assertOk()
            ->assertJsonFragment([
                'data' => [],
            ]);
    }

    public function test_sitemap_empty(): void
    {
        $site = Site::factory()
            ->create([
                'domain' => 'sitemap.empty.example.com',
                'enabled' => false,
            ]);

        $this->getJson(route('api.minisite.sitemap', [$site->domain]))
            ->assertOk()
            ->assertJsonFragment([
                'data' => [],
            ]);
    }

    public function test_sitemap_with_invisible_entries(): void
    {
        Date::setTestNow('2023-01-01 12:00:00');

        $site = Site::factory()
            ->hasPages(3, ['visible' => false])
            ->create([
                'domain' => 'sitemap.empty.example.com',
                'enabled' => false,
            ]);

        $this->getJson(route('api.minisite.sitemap', [$site->domain]))
            ->assertOk()
            ->assertExactJson([
                'data' => [],
            ]);
    }

    public function test_get_page(): void
    {
        $site = Site::factory()
            ->hasPages(2, ['visible' => true])
            ->create();

        $page = $site->pages->first();

        $this->getJson(route('api.minisite.page', [$site->domain, $page->slug]))
            ->assertOk()
            ->assertJsonFragment($page->only('id', 'title', 'slug', 'updated_at'));
    }

    public function test_get_page_invisible(): void
    {
        $site = Site::factory()
            ->hasPages(2, ['visible' => false])
            ->create();

        $page = $site->pages->first();

        $this->getJson(route('api.minisite.page', [$site->domain, $page->slug]))
            ->assertOk()
            ->assertJsonFragment($page->only('id', 'title', 'slug', 'updated_at'));
    }

    public function test_get_page_missing_page(): void
    {
        $site = Site::factory()
            ->create();

        $this->getJson(route('api.minisite.page', [$site->domain, 'does-not-exist']))
            ->assertNotFound();
    }

    public function test_get_page_missing_site(): void
    {
        $this->getJson(route('api.minisite.page', ['not-found.example.com', 'does-not-exist']))
            ->assertNotFound();
    }
}
