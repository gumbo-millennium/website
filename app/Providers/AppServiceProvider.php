<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ConscriboServiceContract;
use App\Contracts\SponsorService as SponsorServiceContract;
use App\Contracts\StripeServiceContract;
use App\Services\ConscriboService;
use App\Services\SponsorService;
use App\Services\StripeService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Laravel\Horizon\Horizon;
use Stripe\Stripe as StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Singleton bindings
     * @var array<string>
     */
    public $singletons = [
        // Sponsor service
        SponsorServiceContract::class => SponsorService::class,
        // Stripe service
        StripeServiceContract::class => StripeService::class,
    ];

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        // Bind Guzzle client
        $this->app->bind(GuzzleClient::class, static fn () => new GuzzleClient(config('gumbo.guzzle-config', [])));

        // Handle Horizon auth
        Horizon::auth(static fn ($request) => $request->user() !== null && $request->user()->hasPermissionTo('devops'));
    }

    /**
     * Register any application services.
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function register()
    {
        // Configure Stripe service
        if ($apiKey = config('stripe.private_key')) {
            // Set key
            StripeClient::setApiKey($apiKey);

            // Retry API calls, a bunch of times
            StripeClient::setMaxNetworkRetries(5);

            // Allow Telemetry (only includes response times)
            StripeClient::setEnableTelemetry(true);
        }

        // Conscribo API
        $this->app->singleton(ConscriboServiceContract::class, static function ($app) {
            // make service
            $service = $app->make(ConscriboService::class, [
                'account' => $app->get('config')->get('services.conscribo.account-name'),
                'username' => $app->get('config')->get('services.conscribo.username'),
                'password' => $app->get('config')->get('services.conscribo.passphrase')
            ]);

            // authenticate
            $service->authorise();

            // return
            return $service;
        });

        // Provide User for all views
        view()->composer('*', static function (View $view) {
            $view->with([
                'sponsorService' => app(SponsorServiceContract::class),
                'user' => request()->user()
            ]);
        });
    }
}
