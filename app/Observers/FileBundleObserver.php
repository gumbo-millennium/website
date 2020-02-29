<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\FileBundle;

class FileBundleObserver
{
    /**
     * Ensures a user is set and a publication date is assigned
     * @param FileBundle $bundle
     * @return void
     */
    public function saving(FileBundle $bundle): void
    {
        // Publish now
        if ($bundle->published_at === null) {
            $bundle->published_at = now();
        }

        // Assign ID, if possible
        if ($bundle->owner_id === null) {
            $bundle->owner_id = optional(request()->user())->id;
        }
    }
}
