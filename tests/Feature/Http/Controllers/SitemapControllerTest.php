<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Activity;
use App\Models\NewsItem;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    public function test_regular_functioning(): void
    {
        $visibleActs = Activity::factory(5)->public()->create();
        $privateActs = Activity::factory(5)->private()->create();
        $publicInvisible = Activity::factory(5)->public()->unpublished()->create();

        $newsItems = NewsItem::factory(5)->create();

        $visiblePages = Page::factory(5)->create();
        $invisiblePages = Page::factory(5)->hidden()->create();

        $result = $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('home'))
            ->assertSee(route('activity.index'))
            ->assertSee(route('news.index'));

        $visibleActs->each(fn (Activity $model) => $result->assertSee(route('activity.show', $model)));
        $privateActs->each(fn (Activity $model) => $result->assertDontSee(route('activity.show', $model)));
        $publicInvisible->each(fn (Activity $model) => $result->assertDontSee(route('activity.show', $model)));

        $newsItems->each(fn (NewsItem $model) => $result->assertSee(route('news.show', $model)));

        $visiblePages->each(fn (Page $model) => $result->assertSee($model->url));
        $invisiblePages->each(fn (Page $model) => $result->assertDontSee($model->url));
    }

    public function test_caching(): void
    {
        Cache::shouldReceive('remember')
            ->withSomeOfArgs('sitemap.index')
            ->andReturn('TEST SITEMAP CONTROLLER');

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('TEST SITEMAP CONTROLLER');
    }
}
