<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ConscriboService as ConscriboServiceContract;
use App\Contracts\EnrollmentServiceContract;
use App\Contracts\MarkdownServiceContract;
use App\Contracts\Payments\PaymentManager;
use App\Contracts\SponsorService as SponsorServiceContract;
use App\Events\EventService;
use App\Services\ConscriboService;
use App\Services\EnrollmentService;
use App\Services\MarkdownService;
use App\Services\Payments\PaymentServiceManager;
use App\Services\SponsorService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Laravel\Horizon\Horizon;
use Spatie\Flash\Flash;
use Symfony\Component\Yaml\Yaml;

class AppServiceProvider extends ServiceProvider
{
    private const ACTIVITY_FEATURES_FILE = 'assets/yaml/activity-features.yaml';

    private const SHOP_FEATURES_FILE = 'assets/yaml/shop-features.yaml';

    /**
     * Singleton bindings.
     *
     * @var array<string>
     */
    public $singletons = [
        // Sponsor service
        SponsorServiceContract::class => SponsorService::class,
    ];

    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string>
     */
    public $bindings = [
        // Enrollment service
        EnrollmentServiceContract::class => EnrollmentService::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Bind Guzzle client
        $this->app->bind(GuzzleClient::class, static fn () => new GuzzleClient(config('gumbo.guzzle-config', [])));

        // Handle Horizon auth
        Horizon::auth(static fn ($request) => $request->user() !== null && $request->user()->hasPermissionTo('devops'));

        // Components
        Blade::component('components.breadcrumbs', 'breadcrumbs');

        // Special events
        Blade::if('event', function ($event) {
            $service = $this->app->make(EventService::class);

            return $service->eventActive($event);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Conscribo API
        $this->app->singleton(ConscriboServiceContract::class, static fn () => ConscriboService::fromConfig());

        // Markdown
        $this->app->singleton(MarkdownServiceContract::class, MarkdownService::class);

        // Events
        $this->app->singleton(EventService::class);

        // Payments
        $this->app->singleton(PaymentManager::class, fn () => PaymentServiceManager::make(
            Config::get('gumbo.payments.default'),
            Config::get('gumbo.payments.providers', []),
        ));

        // Add Paperclip macro to the database helper
        Blueprint::macro('paperclip', function (string $name, ?bool $variants = null) {
            \assert($this instanceof Blueprint);
            $this->string("{$name}_file_name")->comment("{$name} name")->nullable();
            $this->integer("{$name}_file_size")->comment("{$name} size (in bytes)")->nullable();
            $this->string("{$name}_content_type")->comment("{$name} content type")->nullable();
            $this->timestamp("{$name}_updated_at")->comment("{$name} update timestamp")->nullable();

            if ($variants === false) {
                return;
            }

            $this->json("{$name}_variants")->comment("{$name} variants (json)")->nullable();
        });

        // Add Paperclip drop macro to database
        Blueprint::macro('dropPaperclip', function (string $name, ?bool $variants = null) {
            \assert($this instanceof Blueprint);
            $this->dropColumn(array_filter([
                "{$name}_file_name",
                "{$name}_file_size",
                "{$name}_content_type",
                "{$name}_updated_at",
                $variants !== false ? "{$name}_variants" : null,
            ]));
        });

        // Provide User for all views
        view()->composer('*', static function (View $view) {
            $view->with([
                'sponsorService' => app(SponsorServiceContract::class),
                'user' => request()->user(),
            ]);
        });

        // Boot flash settings
        Flash::levels([
            'info' => 'notice notice--info',
            'error' => 'notice notice--warning',
            'warning' => 'notice notice--warning',
            'success' => 'notice notice--brand',
        ]);

        // Bind feature config
        $this->mapFeatures();

        // Registrer Laravel Nova
        $this->registerNova();
    }

    /**
     * Maps features from Yaml files to config.
     */
    private function mapFeatures(): void
    {
        if ($this->app->configurationIsCached()) {
            return;
        }

        $fileMap = [
            self::ACTIVITY_FEATURES_FILE => 'gumbo.activity-features',
            self::SHOP_FEATURES_FILE => 'gumbo.shop.features',
        ];

        foreach ($fileMap as $file => $configKey) {
            foreach (Yaml::parseFile(resource_path($file)) as $feature => $options) {
                $options = array_merge([
                    'title' => null,
                    'icon' => null,
                    'mail' => null,
                    'notice' => null,
                ], $options);

                Config::set("{$configKey}.{$feature}", $options);
            }
        }
    }

    /**
     * Safely registers Nova if it's enabled and available.
     */
    private function registerNova(): void
    {
        // Check if Nova is enabled to begin with
        if (! Config::get('services.features.enable-nova')) {
            return;
        }

        // Check if Nova is available, disable if not
        if (! class_exists(\Laravel\Nova\NovaServiceProvider::class)) {
            Config::set('services.features.enable-nova', false);

            return;
        }

        // Load Nova service provider
        $this->app->register(NovaServiceProvider::class);
    }
}
