<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Mail\MailListHandler;
use App\Services\Mail\GoogleMailListService;
use Google_Client as GoogleApi;
use Google_Exception as GoogleException;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Google_Service_Walletobjects as GoogleWalletService;
use Google_Service_Walletobjects_Eventticketclass_Resource as EventTicketClassResource;
use Google_Service_Walletobjects_Eventticketobject_Resource as EventTicketObjectResource;

class GoogleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GoogleApi::class, function () {
            try {
                $client = new GoogleApi();

                // Apply settings based on key file or default credentials
                $keyFile = Config::get('services.google.key-file');
                if (! $keyFile) {
                    $client->useApplicationDefaultCredentials();
                } else {
                    $client->setAuthConfig($keyFile);
                    $client->setSubject(Config::get('services.google.subject') ?: null);
                }

                // Set scopes and name anyway, they're required
                $client->setScopes(Config::get('services.google.scopes'));
                $client->setApplicationName(Config::get('app.name'));

                return $client;
            } catch (GoogleException $exception) {
                Log::critical('Failed to create Google API client: {exception}', compact('exception'));

                return null;
            }
        });

        /**
         * The google_wallet_api is an entirely separate GoogleApi instance, since
         * the credentials used should not be overlapping with access to Gmail signatures
         * and Google Directory groups.
         */
        $this->app->singleton(GoogleWalletService::class, function () {
            // Log in client as service worker
            $client = new GoogleApi();

            // Apply configs, if enabled
            if (Config::get('services.google.wallet.enabled')) {
                $client->setAuthConfig(Config::get('services.google.wallet.key_file'));
                $client->setApplicationName(Config::get('app.name'));
                $client->setScopes([
                    'https://www.googleapis.com/auth/wallet_object.issuer',
                ]);
            }

            return new GoogleWalletService($client);
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
            // Classes
            GoogleApi::class,

            // Google Wallet
            GoogleWalletService::class,

            // Mail updates
            MailListHandler::class,
        ];
    }
}
