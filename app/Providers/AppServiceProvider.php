<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Activity;
use App\Models\File;
use App\Observers\ActivityObserver;
use App\Observers\FileObserver;
use App\Services\MenuProvider;
use GuzzleHttp\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register nav menu as $menu on all requests
        $this->app->singleton(MenuProvider::class, function () {
            return new MenuProvider();
        });

        // Handle Horizon auth
        Horizon::auth(function ($request) {
            return $request->user() !== null && $request->user()->hasPermissionTo('devops');
        });

        // Handle File, User and Activity changes
        Activity::observe(ActivityObserver::class);
        File::observe(FileObserver::class);

        // Create method to render SVG icons
        Blade::directive('icon', function ($icon, $className = null) {
            $className = $className ?? 'icon';
            return (
                "<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" aria-hidden=\"true\" class=\"{$className}\">" .
                "<use xlink:href=\"<?php echo asset(\"{$icon}\"); ?>\" />" .
                "</svg>"
            );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function register()
    {
        // Add Paperclip macro to the database helper
        Blueprint::macro('paperclip', function (string $name, bool $variants = null) {
            $this->string("{$name}_file_name")->comment("{$name} name")->nullable();
            $this->integer("{$name}_file_size")->comment("{$name} size (in bytes)")->nullable();
            $this->string("{$name}_content_type")->comment("{$name} content type")->nullable();
            $this->timestamp("{$name}_updated_at")->comment("{$name} update timestamp")->nullable();

            if ($variants !== false) {
                $this->json("{$name}_variants")->comment("{$name} variants (json)")->nullable();
            }
        });

        // Add Paperclip drop macro to database
        Blueprint::macro('dropPaperclip', function (string $name, bool $variants = null) {
            $this->dropColumn(array_filter([
                "{$name}_file_name",
                "{$name}_file_size",
                "{$name}_content_type",
                "{$name}_updated_at",
                $variants !== false ? "{$name}_variants" : null
            ]));
        });
    }
}
