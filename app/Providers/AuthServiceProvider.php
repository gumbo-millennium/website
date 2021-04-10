<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Activity;
use App\Models\ActivityMessage;
use App\Models\EmailList;
use App\Models\Enrollment;
use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\FileDownload;
use App\Models\JoinSubmission;
use App\Models\NewsItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Shop\Category;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Shop\ProductVariant;
use App\Models\Sponsor;
use App\Models\User;
use App\Policies\ActivityMessagePolicy;
use App\Policies\ActivityPolicy;
use App\Policies\EmailListPolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\FileBundlePolicy;
use App\Policies\FileCategoryPolicy;
use App\Policies\FileDownloadPolicy;
use App\Policies\JoinSubmissionPolicy;
use App\Policies\NewsItemPolicy;
use App\Policies\PagePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\Shop\CategoryPolicy;
use App\Policies\Shop\OrderPolicy;
use App\Policies\Shop\ProductPolicy;
use App\Policies\Shop\ProductVariantPolicy;
use App\Policies\SponsorPolicy;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        ActivityMessage::class => ActivityMessagePolicy::class,
        EmailList::class => EmailListPolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        FileBundle::class => FileBundlePolicy::class,
        FileCategory::class => FileCategoryPolicy::class,
        FileDownload::class => FileDownloadPolicy::class,
        JoinSubmission::class => JoinSubmissionPolicy::class,
        NewsItem::class => NewsItemPolicy::class,
        Page::class => PagePolicy::class,
        Payment::class => PaymentPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        Sponsor::class => SponsorPolicy::class,
        User::class => UserPolicy::class,

        // Shop
        Order::class => OrderPolicy::class,
        Category::class => CategoryPolicy::class,
        Product::class => ProductPolicy::class,
        ProductVariant::class => ProductVariantPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        // Bind policies
        $this->registerPolicies();

        // Bind admin
        Gate::define('enter-admin', 'App\Gates\AdminGate@nova');

        // Super Admin mode
        $gate->before([$this, 'hasDirectAdminPermission']);
    }

    /**
     * Check if a user has permission to be super admin. Cannot be inherited from roles.
     *
     * @param User $user User to authenticate
     * @return bool|null
     */
    public function hasDirectAdminPermission(User $user): ?bool
    {
        // Check for direct super-admin permission
        if ($user->hasDirectPermission('super-admin')) {
            return true;
        }

        // Return null otherwise
        return null;
    }
}
