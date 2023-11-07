<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\JoinController;
use App\Models\Activity;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class GumboServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the intro event
        $this->app->bind('intro-event', fn () => $this->findGumboIntroEvent());

        // Bind the JoinController to have an Intro event supplied.
        $this->app->when(JoinController::class)
            ->needs(Activity::class)
            ->give('intro-event');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // nothing to do here
    }

    /**
     * Find the Intro event with the tickets preloaded.
     */
    protected function findGumboIntroEvent(): ?Activity
    {
        $currentYear = Date::now()->year;

        // Find the available activity for this year, and it's available tickets
        return Activity::query()
            ->whereAvailable()
            ->where('slug', "intro-{$currentYear}")
            ->with('tickets', fn ($query) => $query->availableFor(null))
            ->first();
    }
}
