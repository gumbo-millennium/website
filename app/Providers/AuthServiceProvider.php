<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models;
use App\Models\Shop as ShopModels;
use App\Models\User;
use App\Policies;
use App\Policies\Shop as ShopPolicies;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Models\Activity::class => Policies\ActivityPolicy::class,
        Models\ActivityMessage::class => Policies\ActivityMessagePolicy::class,
        Models\EmailList::class => Policies\EmailListPolicy::class,
        Models\Enrollment::class => Policies\EnrollmentPolicy::class,
        Models\FileBundle::class => Policies\FileBundlePolicy::class,
        Models\FileCategory::class => Policies\FileCategoryPolicy::class,
        Models\FileDownload::class => Policies\FileDownloadPolicy::class,
        Models\JoinSubmission::class => Policies\JoinSubmissionPolicy::class,
        Models\NewsItem::class => Policies\NewsItemPolicy::class,
        Models\Page::class => Policies\PagePolicy::class,
        Models\Payment::class => Policies\PaymentPolicy::class,
        Models\Permission::class => Policies\PermissionPolicy::class,
        Models\Role::class => Policies\RolePolicy::class,
        Models\Sponsor::class => Policies\SponsorPolicy::class,
        Models\Ticket::class => Policies\TicketPolicy::class,
        Models\User::class => Policies\UserPolicy::class,
        Models\RedirectInstruction::class => Policies\RedirectInstructionPolicy::class,
        Models\Webcam::class => Policies\WebcamPolicy::class,
        Models\WebcamUpdate::class => Policies\WebcamUpdatePolicy::class,

        // Shop
        ShopModels\Order::class => ShopPolicies\OrderPolicy::class,
        ShopModels\Category::class => ShopPolicies\CategoryPolicy::class,
        ShopModels\Product::class => ShopPolicies\ProductPolicy::class,
        ShopModels\ProductVariant::class => ShopPolicies\ProductVariantPolicy::class,

        // Gallery
        Models\Gallery\Album::class => Policies\Gallery\AlbumPolicy::class,
        Models\Gallery\Photo::class => Policies\Gallery\PhotoPolicy::class,
        Models\Gallery\PhotoReaction::class => Policies\Gallery\PhotoReactionPolicy::class,
        Models\Gallery\PhotoReport::class => Policies\Gallery\PhotoReportPolicy::class,
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
