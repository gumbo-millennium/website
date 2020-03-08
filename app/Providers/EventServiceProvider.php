<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\AddVerifiedPermission;
use App\Listeners\CheckConscriboWhenVerified;
use App\Listeners\MediaUploadListener;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\FileBundle;
use App\Models\NewsItem;
use App\Models\Sponsor;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\FileBundleObserver;
use App\Observers\NewsItemObserver;
use App\Observers\SponsorObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\Events\MediaHasBeenAdded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     * @var array
     */
    protected $listen = [
        MediaHasBeenAdded::class => [
            MediaUploadListener::class
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Verified::class => [
            CheckConscriboWhenVerified::class,
            AddVerifiedPermission::class
        ]
    ];

    /**
     * Register any events for your application.
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register observers
        Activity::observe(ActivityObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        FileBundle::observe(FileBundleObserver::class);
        NewsItem::observe(NewsItemObserver::class);
        Sponsor::observe(SponsorObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Auto-discover events
     * @return true
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }
}
