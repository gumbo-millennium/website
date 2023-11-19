<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Minisite;

use App\Models\Minisite\Site;
use App\Models\Minisite\SitePage;
use Tests\TestCase;

class SitePageTest extends TestCase
{
    /**
     * Ensure slugs are only unique in their own site.
     */
    public function test_slugs_across_websites_work_as_expected(): void
    {
        [$site1, $site2] = Site::factory(2)->create();

        $basePage = SitePage::factory()->for($site1)->create();
        $duplicatePage = SitePage::factory()->for($site1)->create(['title' => $basePage->title]);
        $uniquePage = SitePage::factory()->for($site1)->create();

        $basePageDiffSite = SitePage::factory()->for($site2)->create(['title' => $basePage->title]);

        $this->assertEquals($basePage->title, $duplicatePage->title);
        $this->assertNotEquals($basePage->title, $uniquePage->title);
        $this->assertEquals($basePage->title, $basePageDiffSite->title);

        $this->assertNotEquals($basePage->slug, $duplicatePage->slug);
        $this->assertNotEquals($basePage->slug, $uniquePage->slug);
        $this->assertEquals($basePage->slug, $basePageDiffSite->slug);
    }
}
