<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use LogicException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        // Forward to parent
        parent::boot();

        // Check if locally developing, and then set the root URL if required
        if ($this->app->environment('local')) {
            $this->updateLocalRootUrl();
        }
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        // Web routes
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->name('api.')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));

        Route::prefix('api')
            ->middleware('api-webhooks')
            ->name('api.')
            ->namespace($this->namespace)
            ->group(base_path('routes/webhooks.php'));
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Allow api-expensive calls just once per minute
        RateLimiter::for('api-expensive', fn (Request $request) => Limit::perMinute(1)->by($request->user()?->id ?: $request->ip()));
    }

    /**
     * Updates the URL generator's root URL if a HMR program is running an an X-Forwarded-Host is provided.
     */
    protected function updateLocalRootUrl(): void
    {
        // We /really/ don't want this in production.
        if (! $this->app->environment('local')) {
            $exception = new LogicException('An attempt to update the root URL on a non-local environment was issued.');
            logger()->critical('Security violation: {exception}', compact('exception'));

            throw $exception;
        }

        // Only run when HMR is active
        if (! file_exists(public_path('hot'))) {
            return;
        }

        // Check for required header
        $newUrl = request()->headers->get('X-Forwarded-Host');
        if (empty($newUrl)) {
            return;
        }

        // Get URL handler and check type
        $urlGenerator = app('url');
        if (! $urlGenerator instanceof LaravelUrlGenerator && ! method_exists($urlGenerator, 'forceRootUrl')) {
            return;
        }

        // Set new URL
        $urlGenerator->forceRootUrl("http://{$newUrl}/");

        // Log result
        logger()->debug('Replaced host with user-provided path.', [
            'new-host' => $newUrl,
        ]);
    }
}
