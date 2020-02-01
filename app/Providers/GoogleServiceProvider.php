<?php

namespace App\Providers;

use Google_Client as GoogleApi;
use Google_Exception as GoogleException;
use Google_Service_Directory as GoogleDirectory;
use Google_Service_Directory_Aliases as GoogleDirectoryAliases;
use Google_Service_Directory_Groups as GoogleDirectoryGroups;
use Google_Service_Directory_Members as GoogleDirectoryMembers;
use Google_Service_Exception as GoogleServiceException;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GoogleApi::class, function ($app) {
            try {
                // Config
                $config = $app->get('config');

                // Log in client as service worker
                $client = new GoogleApi();

                // Apply configs
                $client->setAuthConfig($config->get('gumbo.google.auth.key-file'));
                $client->setApplicationName($config->get('app.name'));
                $client->setSubject($config->get('gumbo.google.auth.subject'));
                $client->setScopes($config->get('gumbo.google.auth.scopes'));

                // Return client
                return $client;
            } catch (GoogleException $exception) {
                // Log the error
                logger()->critical('Failed to create Google API client: {exception}', compact('exception'));

                // Return null
                return null;
            }
        });

        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->app->singleton(GoogleDirectory::class, fn($app) => new GoogleDirectory($app->get(GoogleApi::class)));
        $this->app->singleton(GoogleDirectoryGroups::class, fn($app) => new GoogleDirectoryGroups($app->get(GoogleDirectory::class)));
        $this->app->singleton(GoogleDirectoryAliases::class, fn($app) => new GoogleDirectoryAliases($app->get(GoogleDirectory::class)));
        $this->app->singleton(GoogleDirectoryMembers::class, fn($app) => new GoogleDirectoryMembers($app->get(GoogleDirectory::class)));
        // phpcs:enable
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            GoogleApi::class,
            GoogleDirectory::class,
            GoogleDirectoryGroups::class,
            GoogleDirectoryAliases::class,
            GoogleDirectoryMembers::class,
        ];
    }
}
