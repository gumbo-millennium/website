<?php

declare(strict_types=1);

namespace App\Policies\Minisite;

use App\Enums\Models\Minisite\PageType;
use App\Models\Minisite\Site;
use App\Models\Minisite\SitePage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SitePagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->can('viewAny', Site::class);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SitePage $page): Response|bool
    {
        return $user->can('view', $page->site);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, SitePage $page): bool
    {
        return $this->view($user, $page);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SitePage $page): Response|bool
    {
        if (! $user->can('view', $page)) {
            return $this->deny('You are not authorized to delete this page.');
        }

        if ($page->type === PageType::Required) {
            return $this->deny('You cannot delete a required page.');
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SitePage $page): Response|bool
    {
        if (! $user->can('delete', $page)) {
            return $this->deny('You are not authorized to restore this page.');
        }

        return true;
    }

    /**
     * Nobody can force-delete pages, they're auto-deleted after 30 days.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function forceDelete(User $user, SitePage $page): Response
    {
        return $this->deny('Pages are automatically deleted after 30 days.');
    }
}
