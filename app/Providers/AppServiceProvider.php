<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\StripeErrorService;
use App\Services\MenuProvider;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Horizon\Horizon;
use Spatie\Flash\Flash;
use Stripe\Stripe as StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Add Stripe error handler service
        $this->app->bind(StripeErrorService::class);

        // Register nav menu as $menu on all requests
        $this->app->singleton(MenuProvider::class, function () {
            return new MenuProvider();
        });

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
        // Add Str macros
        $validNumber = function ($value): ?float {
            // Validate number value
            $number = filter_var($value, FILTER_VALIDATE_FLOAT);

            // Skip if empty
            return ($number === false) ? null : $number;
        };

        // Number formatting
        Str::macro('number', function ($value, int $decimals = 0) use ($validNumber) {
            // Validate number and return null if empty
            $value = $validNumber($value);

            // Return formatted number, if set
            return ($value === null) ? null :  number_format($value, $decimals, ',', '.');
        });

        // Price formatting
        Str::macro('price', function ($value, bool $decimals = null) use ($validNumber) {
            // Validate number and return null if empty
            $value = $validNumber($value);
            if ($value === null) {
                return null;
            }

            $value /= 100;
            $prefix = ($value < 0) ? '-' : '';

            // Handle round value value
            if ($decimals === false || (($value * 100) % 100 === 0 && $decimals !== true)) {
                return sprintf('%s€ %s,-', $prefix, number_format(abs($value), 0, ',', '.'));
            }

            // Handle decimal value
            return sprintf('%s€ %s', $prefix, number_format(abs($value), 2, ',', '.'));
        });

        // List formatting
        Arr::macro('implode', function ($value) {
            // No 'and' needed
            if (count($value) <= 1) {
                return implode(', ', $value);
            }

            // Pull off last item
            $last = array_pop($value);

            // Merge them
            return implode(', ', $value) . ' en ' . $last;
        });
    }
}
