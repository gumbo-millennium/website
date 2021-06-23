<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'content-admin';

    /**
     * Determine whether the user can view any pages.
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', Page::class);
    }

    /**
     * Determine whether the user can view the page.
     */
    public function view(User $user)
    {
        return $user->can('manage', Page::class);
    }

    /**
     * Determine whether the user can create pages.
     */
    public function create(User $user)
    {
        return $user->can('manage', Page::class);
    }

    /**
     * Determine whether the user can update the page.
     */
    public function update(User $user, Page $page)
    {
        return $user->can('manage', Page::class) && $page->type !== Page::TYPE_GIT;
    }

    /**
     * Determine whether the user can delete the page.
     */
    public function delete(User $user, Page $page)
    {
        return $user->can('manage', Page::class) && $page->type === Page::TYPE_USER;
    }

    /**
     * Determine whether the user can restore the page.
     */
    public function restore(User $user)
    {
        return $user->can('manage', Page::class);
    }

    /**
     * Determine whether the user can permanently delete the page.
     */
    public function forceDelete(User $user)
    {
        return $user->can('manage', Page::class);
    }

    /**
     * Returns if the user is allowed to edit pages and news articles.
     *
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
