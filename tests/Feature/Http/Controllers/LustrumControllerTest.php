<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\LustrumController;
use App\Models\Activity;
use App\Models\Page;
use App\Models\Role;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LustrumControllerTest extends TestCase
{
    use WithFaker;

    private const LUSTRUM_DOMAIN = 'gumbolustrum.nl';

    private const LUSTRUM_HOST = 'http://gumbolustrum.nl';

    /**
     * Ensure the minisite is setup correctly.
     * @before
     */
    public function setupMinisiteBeforeTest(): void
    {
        $this->afterApplicationCreated(function () {
            $minisites = Config::get('gumbo.minisites');
            if (! array_key_exists(self::LUSTRUM_DOMAIN, $minisites)) {
                $this->markTestSkipped('Lustrum minisite is not enabled');
            }

            $configuredController = $minisites[self::LUSTRUM_DOMAIN]['controller'] ?? null;
            if ($configuredController !== LustrumController::class) {
                $this->fail(sprintf(
                    'Lustrum minisite is configured to use controller [%s], where [%s] was expected',
                    $configuredController,
                    LustrumController::class,
                ));
            }

            Config::set('gumbo.minisites', [
                self::LUSTRUM_DOMAIN => [
                    'enabled' => true,
                    'controller' => LustrumController::class,
                ],
            ]);
        });
    }

    public function test_get_index(): void
    {
        $this->get(self::LUSTRUM_HOST)
            ->assertOk()
            ->assertSee('Er is er een jarig');
    }

    public function test_with_proper_activities(): void
    {
        $lustrumRole = Role::findOrCreate('lucie');
        assert($lustrumRole instanceof Role);

        $activities = Activity::factory()->create([
            'role_id' => $lustrumRole->getKey(),
        ]);

        $this->get(self::LUSTRUM_HOST)
            ->assertOk()
            ->assertSee($activities->name);
    }

    public function test_with_custom_page(): void
    {
        // Delete existing page
        optional(Page::findBySlug('lustrum'))->delete();

        // Make a new one
        $lustrumPage = Page::factory()
            ->withContents()
            ->withSummary()
            ->create(['slug' => 'lustrum']);

        $this->get(self::LUSTRUM_HOST)
            ->assertOk()
            ->assertSee($lustrumPage->title)
            ->assertSee($lustrumPage->summary)
            ->assertSee($lustrumPage->html->toHtml(), false);
    }

    public function test_with_merchandise(): void
    {
        /** @var Category $shopCategory */
        $shopCategory = Category::factory()
            ->has(Product::factory(4)->hasVariants()->visible())
            ->visible()
            ->create(['slug' => 'lustrum']);

        /** @var Collection<Product> $shopProducts */
        $shopProducts = $shopCategory->products;

        $result = $this->get(self::LUSTRUM_HOST)
            ->assertOk();

        foreach ($shopProducts as $product) {
            $result->assertSee($product->name);
            $result->assertSee(route('shop.product', $product));
        }
    }

    public function test_get_main_site_routes(): void
    {
        $this->get(self::LUSTRUM_HOST . route('activity.index', [], false))
            ->assertNotFound();

        $this->get(self::LUSTRUM_HOST . route('account.index', [], false))
            ->assertNotFound();

        $this->get(self::LUSTRUM_HOST . route('login', [], false))
            ->assertNotFound();
    }
}
