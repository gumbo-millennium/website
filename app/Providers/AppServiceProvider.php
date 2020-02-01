<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\StripeServiceContract;
use App\Helpers\Arr;
use App\Helpers\Str;
use App\Services\MenuProvider;
use App\Services\StripeErrorService;
use App\Services\StripeService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Laravel\Horizon\Horizon;
use Spatie\Flash\Flash;
use Stripe\Stripe as StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Singleton bindings
     * @var string[]
     */
    public $singletons = [
        // Stripe service
        StripeServiceContract::class => StripeService::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Bind Guzzle client
        $this->app->bind(GuzzleClient::class, function () {
            return new GuzzleClient(config('gumbo.guzzle-config', []));
        });

        // Handle Horizon auth
        Horizon::auth(function ($request) {
            return $request->user() !== null && $request->user()->hasPermissionTo('devops');
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
        // Configure Stripe service
        if ($apiKey = config('stripe.private_key')) {
            // Set key
            StripeClient::setApiKey($apiKey);

            // Retry API calls, a bunch of times
            StripeClient::setMaxNetworkRetries(5);

            // Allow Telemetry (only includes response times)
            StripeClient::setEnableTelemetry(true);
        }

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

        // Boot string macros
        $this->bootStrMacros();

        // Provide User for all views
        view()->composer('*', function (View $view) {
            $view->with([
                'user' => request()->user()
            ]);
        });

        // Boot flash settings
        Flash::levels([
            'info' => "site-wide-banner__container--info",
            'error' => "site-wide-banner__container--error",
            'warning' => "site-wide-banner__container--warning",
            'success' => "site-wide-banner__container--success",
        ]);
    }

    /**
     * Adds macros for number formatting to the Str helper
     *
     * @return void
     */
    private function bootStrMacros(): void
    {
    }
}
