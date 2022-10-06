<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models;
use App\Nova\Dashboards;
use App\Nova\Resources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Ensure timezone is Europe/Amsterdam
        Nova::userTimezone(static fn () => 'Europe/Amsterdam');

        // Disable the notification center for now
        Nova::withoutNotificationCenter();

        // Create menu
        $this->createMenu();
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', 'App\\Gates\\AdminGate@nova');
    }

    /**
     * Register the application's Nova resources.
     *
     * @return void
     */
    protected function resources()
    {
        Nova::resourcesIn(app_path('Nova/Resources'));
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new Dashboards\Main(),
        ];
    }

    /**
     * Ensure the menu of Nova is a bit prettier, not just one enormous list of items.
     */
    private function createMenu(): void
    {
        Nova::mainMenu(function (Request $request) {
            $user = $request->user();

            yield MenuItem::externalLink('Naar website', URL::to('/'));

            yield MenuSection::dashboard(Dashboards\Main::class)->icon('chart-bar');

            if ($user?->can('viewAny', Models\Activity::class)) {
                yield MenuSection::make(__('Activities'), [
                    MenuItem::resource(Resources\Activity::class),
                    MenuItem::resource(Resources\Enrollment::class),
                ])->icon('calendar')->collapsable();
            }

            if ($user?->can('viewAny', Models\Page::class)) {
                yield MenuSection::make(__('Content'), [
                    MenuItem::resource(Resources\Page::class),
                    MenuItem::resource(Resources\NewsItem::class),
                    MenuItem::resource(Resources\RedirectInstruction::class),
                ])->icon('collection')->collapsable();
            }

            if ($user?->can('viewAny', Models\User::class)) {
                yield MenuSection::make('Bestuurszaken', [
                    MenuItem::resource(Resources\User::class),
                    MenuItem::resource(Resources\EmailList::class),
                    MenuItem::resource(Resources\JoinSubmission::class),
                    MenuItem::resource(Resources\Sponsor::class),
                    MenuItem::resource(Resources\Payments\Settlement::class),
                ])->icon('users')->collapsable();
            }

            if ($user->can('viewAny', Models\Webcam\Camera::class)) {
                yield MenuSection::make('Apparaten', [
                    MenuItem::resource(Resources\Webcam\Camera::class),
                    MenuItem::resource(Resources\Webcam\Device::class),
                ])->icon('camera')->collapsable();
            }

            if ($user?->can('viewAny', Models\FileBundle::class)) {
                yield MenuSection::make('Documentensysteem', [
                    MenuItem::resource(Resources\FileBundle::class),
                    MenuItem::resource(Resources\FileCategory::class),
                ])->icon('library')->collapsable();
            }

            if ($user?->can('viewAny', Models\Shop\Order::class)) {
                yield MenuSection::make(__('Shop'), [
                    MenuItem::resource(Resources\Shop\Order::class),
                    MenuItem::resource(Resources\Shop\Category::class),
                    MenuItem::resource(Resources\Shop\ProductVariant::class),
                    MenuItem::resource(Resources\Shop\Product::class),
                ])->icon('shopping-cart')->collapsable();
            }

            if ($user?->can('devops')) {
                yield MenuSection::make(__('DevOps'), [
                    MenuItem::externalLink('Telescope', URL::to(Config::get('telescope.path'))),
                    MenuItem::externalLink('Horizon', URL::to(Config::get('horizon.path'))),
                ])->icon('server');
            }
        });
    }
}
