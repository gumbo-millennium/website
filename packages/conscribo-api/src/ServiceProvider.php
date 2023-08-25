<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/conscribo-api.php', 'services');

        $this->app->singleton(Contracts\ConscriboApiClient::class, fn ($app) => new ConscriboApiClient($app['config']['services.conscribo']));
    }
}
