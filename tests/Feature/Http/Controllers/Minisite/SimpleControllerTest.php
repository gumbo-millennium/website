<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Minisite;

use App\Models\Page;
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
            Page::factory()->create([
                'slug' => 'about',
                'title' => 'Test Site AboutPage Localhost',
                'group' => 'test-site.localhost',
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
}
