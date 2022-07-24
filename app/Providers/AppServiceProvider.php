<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ConscriboService as ConscriboServiceContract;
use App\Contracts\EnrollmentServiceContract;
use App\Contracts\MarkdownServiceContract;
use App\Contracts\Payments\PaymentManager;
use App\Contracts\SponsorService as SponsorServiceContract;
use App\Services\ConscriboService;
use App\Services\EnrollmentService;
use App\Services\EventService;
use App\Services\GalleryService;
use App\Services\MarkdownService;
use App\Services\Payments\PaymentServiceManager;
use App\Services\SponsorService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Laravel\Horizon\Horizon;
use Spatie\Flash\Flash;
use Symfony\Component\Process\Process;
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
        // Gallery
        GalleryService::class,
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

        // Provide User for all views
        view()->composer('*', static function (View $view) {
            $view->with([
                'sponsorService' => app(SponsorServiceContract::class),
                'user' => request()->user(),
            ]);
        });

        // Boot flash settings
        Flash::levels([
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            'danger' => 'danger',
        ]);

        if (! App::configurationIsCached()) {
            $this->registerFileBasedConfig();
            $this->registerVersionConfig();
        }
    }

    /**
     * Maps configuration values from Yaml files to config.
     */
    private function registerFileBasedConfig(): void
    {
        $featureFiles = [
            self::ACTIVITY_FEATURES_FILE => 'gumbo.activity-features',
            self::SHOP_FEATURES_FILE => 'gumbo.shop.features',
        ];

        foreach ($featureFiles as $file => $configKey) {
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

        $plainFiles = [
            'yaml/menu.yaml' => 'gumbo.layout',
            'yaml/buttons.yaml' => 'gumbo.buttons',
            'yaml/account-menu.yaml' => 'gumbo.account.menu',
            'yaml/grants.yaml' => 'gumbo.account.grants',
        ];

        foreach ($plainFiles as $file => $configKey) {
            $options = Yaml::parseFile(resource_path($file));
            Config::set($configKey, array_merge(Config::get($configKey, []), $options));
        }
    }

    /**
     * Loads version information from Git.
     */
    private function registerVersionConfig(): void
    {
        $versionProcess = Process::fromShellCommandline('git log -1 --format=\'%h\'');
        $versionProcess->run();

        if (! $versionProcess->isSuccessful()) {
            Config::set('gumbo.version', date('YmdHi'));

            return;
        }

        Config::set('gumbo.version', trim($versionProcess->getOutput()));
    }
}
