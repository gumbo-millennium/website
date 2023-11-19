<?php

declare(strict_types=1);

use App\Models\Minisite\Site;
use App\Models\Minisite\SitePage;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MoveMinisitePagesToSitePages extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pagesToMove = Page::query()
            ->with('author')
            ->whereNotNull('group')
            ->whereNotIn('group', array_keys(config('gumbo.page-groups')))
            ->get();

        $requiredSiteNames = $pagesToMove->pluck('group')->unique();
        $createdSites = [];

        foreach ($requiredSiteNames as $siteName) {
            $site = Site::make([
                'domain' => $siteName,
                'name' => $siteName,
                'disabled' => true,
            ]);

            $pageWithAuthor = $pagesToMove->where('group', $siteName)
                ->sortBy('created_at')
                ->firstWhere('author', '!=', null);

            if ($pageWithAuthor) {
                $site->createdBy()->associate($pageWithAuthor->author);
                $site->updatedBy()->associate($pageWithAuthor->author);
            }

            $site->save();
            $createdSites[$siteName] = $site;
        }

        foreach ($pagesToMove as $page) {
            $site = $createdSites[$page->group];
            DB::transaction(function () use ($site, $page) {
                $this->migratePageToSitePage($site, $page);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pagesToMove = SitePage::query()->with('site')->get();

        foreach ($pagesToMove as $page) {
            DB::transaction(function () use ($page) {
                $this->migrateSitePageToPage($page);
            });
        }
    }

    /**
     * Create a new SitePage from the existing page.
     */
    private function migratePageToSitePage(Site $site, Page $page): void
    {
        $sitePage = new SitePage([
            'title' => $page->title,
            'slug' => $page->slug,
            'contents' => $page->contents,
            'cover' => $page->cover,
        ]);

        if ($page->author) {
            $sitePage->createdBy()->associate($page->author);
            $sitePage->updatedBy()->associate($page->author);
        }

        $site->pages()->save($sitePage);

        if ($page->type !== Page::TYPE_USER) {
            $page->type = Page::TYPE_USER;
            $page->save();
        }
    }

    /**
     * Restore a Page for the given SitePage, in the group specified.
     */
    private function migrateSitePageToPage(SitePage $sitePage): void
    {
        $page = Page::firstOrNew([
            'group' => $sitePage->site->domain,
            'slug' => $sitePage->slug,
        ]);

        $page->forceFill($sitePage->only([
            'title',
            'cover',
            'contents',
        ]));
        $page->hidden = ! $sitePage->visible;

        if ($sitePage->createdBy) {
            $page->author()->associate($sitePage->createdBy);
        }

        $page->save();
    }
}
