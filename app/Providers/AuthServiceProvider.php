<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\FileDownload;
use App\Models\JoinSubmission;
use App\Models\NewsItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Sponsor;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\FileCategoryPolicy;
use App\Policies\FileDownloadPolicy;
use App\Policies\FilePolicy;
use App\Policies\JoinSubmissionPolicy;
use App\Policies\NewsItemPolicy;
use App\Policies\PagePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SponsorPolicy;
use App\Policies\UserPolicy;
use Closure;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        File::class => FilePolicy::class,
        FileCategory::class => FileCategoryPolicy::class,
        FileDownload::class => FileDownloadPolicy::class,
        JoinSubmission::class => JoinSubmissionPolicy::class,
        NewsItem::class => NewsItemPolicy::class,
        Page::class => PagePolicy::class,
        Payment::class => PaymentPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        Sponsor::class => SponsorPolicy::class,
        User::class => UserPolicy::class
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
        $gate->before([$this, 'hasDirectAdminPermission']);
    }

    /**
     * Check if a user has permission to be super admin. Cannot be inherited from roles.
     *
     * @param User $user User to authenticate
     * @param mixed $ability (unused)
     * @return bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function hasDirectAdminPermission(User $user, $ability): ?bool
    {
        // Check for direct super-admin permission
        if ($user->hasDirectPermission('super-admin')) {
            return true;
        }

        // Return null otherwise
        return null;
    }
}
