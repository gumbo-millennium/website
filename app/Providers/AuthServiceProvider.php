<?php

namespace App\Providers;

use App\Models\File;
use App\Models\FileCategory;
use App\Policies\FileCategoryPolicy;
use App\Policies\FilePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\JoinRequest;
use App\Policies\JoinRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        File::class => FilePolicy::class,
        FileCategory::class => FileCategoryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
