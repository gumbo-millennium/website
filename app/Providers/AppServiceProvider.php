<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ConscriboServiceContract;
use App\Contracts\EnrollmentServiceContract;
use App\Contracts\SponsorService as SponsorServiceContract;
use App\Contracts\StripeServiceContract;
use App\Services\ConscriboService;
use App\Services\EnrollmentService;
use App\Services\SponsorService;
use App\Services\StripeService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Laravel\Horizon\Horizon;
use Spatie\Flash\Flash;
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
        // Enrollment service
        EnrollmentServiceContract::class => EnrollmentService::class,
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

        // Add Paperclip macro to the database helper
        Blueprint::macro('paperclip', function (string $name, ?bool $variants = null) {
            \assert($this instanceof Blueprint);
            $this->string("{$name}_file_name")->comment("{$name} name")->nullable();
            $this->integer("{$name}_file_size")->comment("{$name} size (in bytes)")->nullable();
            $this->string("{$name}_content_type")->comment("{$name} content type")->nullable();
            $this->timestamp("{$name}_updated_at")->comment("{$name} update timestamp")->nullable();

            if ($variants !== false) {
                $this->json("{$name}_variants")->comment("{$name} variants (json)")->nullable();
            }
        });

        // Add Paperclip drop macro to database
        Blueprint::macro('dropPaperclip', function (string $name, ?bool $variants = null) {
            \assert($this instanceof Blueprint);
            $this->dropColumn(array_filter([
                "{$name}_file_name",
                "{$name}_file_size",
                "{$name}_content_type",
                "{$name}_updated_at",
                $variants !== false ? "{$name}_variants" : null
            ]));
        });

        // Provide User for all views
        view()->composer('*', static function (View $view) {
            $view->with([
                'sponsorService' => app(SponsorServiceContract::class),
                'user' => request()->user()
            ]);
        });

        // Boot flash settings
        Flash::levels([
            'info' => "notice notice--info",
            'error' => "notice notice--warning",
            'warning' => "notice notice--warning",
            'success' => "notice notice--brand",
        ]);

        // Bind Laravel Nova if it's available
        if (Config::get('services.features.enable-nova')) {
            $this->app->register(NovaServiceProvider::class);
        }
    }
}
