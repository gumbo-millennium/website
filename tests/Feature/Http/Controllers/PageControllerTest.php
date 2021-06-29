<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Page;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Finder\Finder;
use Tests\Feature\Http\Controllers\Shop\Traits\TestsShop;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use TestsShop;

    private const GIT_FILE = 'privacy-policy';

    public function test_empty_homepage(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_full_homepage(): void
    {
        [$public1, $private, $public2] = factory(Activity::class)->createMany([[
            'is_public' => true,
            'start_date' => $start = Date::now()->addWeeks(1),
            'end_date' => (clone $start)->addHours(3),
            'published_at' => null,
        ], [
            'is_public' => false,
            'start_date' => $start = Date::now()->addWeeks(2),
            'end_date' => (clone $start)->addHours(3),
            'published_at' => null,
        ], [
            'is_public' => true,
            'start_date' => $start = Date::now()->addWeeks(3),
            'end_date' => (clone $start)->addHours(3),
            'published_at' => null,
        ]]);

        $advertisedProduct = $this->getProductVariant()->product;
        $advertisedProduct->advertise_on_home = true;
        $advertisedProduct->save();

        $this->get('/')
            ->assertOk()
            ->assertSeeText($advertisedProduct->name)
            ->assertSeeText($public1->name)
            ->assertDontSeeText($private->name)
            ->assertSeeText($public2->name);

        $this->actingAs($this->getGuestUser())
            ->get('/')
            ->assertOk()
            ->assertSeeText($advertisedProduct->name)
            ->assertSeeText($public1->name)
            ->assertDontSeeText($private->name)
            ->assertSeeText($public2->name);

        $this->actingAs($this->getMemberUser())
            ->get('/')
            ->assertOk()
            ->assertSeeText($advertisedProduct->name)
            ->assertSeeText($public1->name)
            ->assertSeeText($private->name)
            ->assertDontSeeText($public2->name);
    }

    public function test_git_page(): void
    {
        $this->artisan('gumbo:update-content');

        $file = self::GIT_FILE;

        $finder = Finder::create()
            ->in(resource_path('assets/json/pages'))
            ->name('*.json')
            ->files();

        $pageTable = (new Page())->getTable();

        foreach ($finder as $file) {
            $this->assertFileExists($file->getPathname());

            $slug = Str::beforeLast($file->getFilename(), '.');

            $this->assertDatabaseHas($pageTable, [
                'slug' => $slug,
            ]);

            $this->get("/${slug}")->assertOk();
        }
    }
}
