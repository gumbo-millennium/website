<?php

declare(strict_types=1);

namespace App\Gates;

use App\Models\Activity;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\FileDownload;
use App\Models\JoinSubmission;
use App\Models\NewsItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Support\Facades\Config;

/**
 * Handles authorisation for users entering admin areas
 */
class AdminGate
{
    /**
     * Returns if the user can enter Nova
     *
     * @param User $user
     * @return bool
     */
    public function nova(User $user): bool
    {
        // Disallow if nova isn't available
        if (!Config::get('services.features.enable-nova')) {
            return false;
        }

        return $user->can('create', Activity::class)
            || $user->can('manage', Activity::class)
            || $user->can('manage', File::class)
            || $user->can('manage', FileCategory::class)
            || $user->can('manage', FileDownload::class)
            || $user->can('manage', JoinSubmission::class)
            || $user->can('manage', NewsItem::class)
            || $user->can('manage', Page::class)
            || $user->can('manage', Payment::class)
            || $user->can('manage', Sponsor::class)
            || $user->can('manage', User::class);
    }

    public function devops(User $user): bool
    {
        return $user->hasPermissionTo('devops');
    }
}
