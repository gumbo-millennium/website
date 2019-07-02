<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\File;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Observers\FileObserver;
use App\Observers\UserObserver;
use App\Services\MenuProvider;
use GuzzleHttp\Client;
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
        User::observe(UserObserver::class);

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
     */
    public function register()
    {
        // Register Guzzle client (singleton)
        $this->app->singleton(Client::class, function () {

            // Publish our name, some version info and how to contact us.
            $userAgent = sprintf(
                'gumbo-millennium.nl/1.0 (incompatible; curl/%s; php/%s; https://www.gumbo-millennium.nl);',
                curl_version()['version'],
                PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION
            );

            // The client should send a user agent that allows sysadmins to contact us,
            // aside from that we should be snappy with declining the connection and not
            // throw exceptions on response codes ≥ 400.
            return new Client([
                'http_errors' => false,
                'connect_timeout' => 0.50,
                'headers' => ["User-Agent: {$userAgent}"]
            ]);
        });
    }
}
