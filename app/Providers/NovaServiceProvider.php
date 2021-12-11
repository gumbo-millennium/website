<?php

declare(strict_types=1);

namespace App\Providers;

use App\Nova\Metrics\NewEnrollments;
use App\Nova\Metrics\NewUsers;
use App\Nova\Resources\Permission;
use App\Nova\Resources\Role;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Vyuldashev\NovaPermission\NovaPermissionTool;

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
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            // Permission handler
            NovaPermissionTool::make()
                ->roleResource(Role::class)
                ->permissionResource(Permission::class),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register advoor/nova-editor-js
        $this->app->register(\Advoor\NovaEditorJs\FieldServiceProvider::class);

        // Register benjaminhirsch/nova-slug-field
        $this->app->register(\Benjaminhirsch\NovaSlugField\FieldServiceProvider::class);

        // Register ebess/advanced-nova-media-library
        $this->app->register(\Ebess\AdvancedNovaMediaLibrary\AdvancedNovaMediaLibraryServiceProvider::class);

        // Register kabbouchi/nova-impersonate
        $this->app->register(\KABBOUCHI\NovaImpersonate\ToolServiceProvider::class);

        // Register vyuldashev/nova-permission
        $this->app->register(\Vyuldashev\NovaPermission\ToolServiceProvider::class);

        // Register whitecube/nova-flexible-content
        $this->app->register(\Whitecube\NovaFlexibleContent\FieldServiceProvider::class);
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
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new NewUsers(),
            new NewEnrollments(),
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [];
    }
}
