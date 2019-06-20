<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\Payment;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\FileCategoryPolicy;
use App\Policies\FilePolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        File::class => FilePolicy::class,
        FileCategory::class => FileCategoryPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies();

        // Super Admin mode
        $gate->before(function (User $user, $ability) {
            if ($user->hasDirectPermission('super-admin')) {
                return true;
            }
        });
    }
}
