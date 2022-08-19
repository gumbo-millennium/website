<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Mail\MailListHandler;
use App\Services\Mail\GoogleMailListService;
use Google_Client as GoogleApi;
use Google_Exception as GoogleException;
use Illuminate\Config\Repository as ConfigRepository;
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
        $this->app->singleton(GoogleApi::class, static function ($app) {
            try {
                // Config
                $config = $app['config'];
                \assert($config instanceof ConfigRepository);

                // Log in client as service worker
                $client = new GoogleApi();

                // Apply configs
                $client->setAuthConfig($config->get('services.google.key-file'));
                $client->setScopes($config->get('services.google.scopes'));
                $client->setSubject($config->get('services.google.subject'));
                $client->setApplicationName($config->get('app.name'));

                // Return client
                return $client;
            } catch (GoogleException $exception) {
                // Log the error
                logger()->critical('Failed to create Google API client: {exception}', compact('exception'));

                // Return null
                return null;
            }
        });

        $this->app->singleton('google_wallet_api', function ($app) {
            $config = $app->get('config');
            assert($config instanceof ConfigRepository);

            // Log in client as service worker
            $client = new GoogleApi();

            // Apply configs
            $client->setAuthConfig($config->get('services.google.wallet.key_file'));
            $client->setApplicationName($config->get('app.name'));
            $client->setScopes([
                'https://www.googleapis.com/auth/wallet_object.issuer',
            ]);

            return $client;
        });

        // Mail
        $this->app->singleton(MailListHandler::class, GoogleMailListService::class);
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
            MailListHandler::class,
        ];
    }
}
