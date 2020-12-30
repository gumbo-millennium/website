<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Don't run when testing
        if ($this->app->environment('testing')) {
            return;
        }

        // Enable the night mode
        Telescope::night();

        // Ensure special values are hidden
        $this->hideSensitiveRequestDetails();

        // Allow logging everything on local and beta
        if ($this->app->isLocal() || Config::get('gumbo.beta')) {
            return true;
        }

        // Filter batches
        Telescope::filterBatch(static function (Collection $entries) {
            // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
            $entries->contains(static function (IncomingEntry $entry) {
                return
                    $entry->isReportableException() ||
                    $entry->isFailedRequest() ||
                    $entry->isFailedJob() ||
                    $entry->isScheduledTask() ||
                    $entry->hasMonitoredTag();
            });
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        // Hiden tokens
        Telescope::hideRequestParameters(['_token', 'password']);

        // Hide cookies and CSRF tokens
        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', static fn ($user) => $user && $user->hasPermissionTo('devops'));
    }
}
