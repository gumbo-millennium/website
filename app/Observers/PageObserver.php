<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Page;

class PageObserver
{
    /**
     * Handle the page "created" event.
     *
     * @param \App\Page $page
     * @return void
     */
    public function saving(Page $page)
    {
        // Assign changing user to object
        if (! $user = auth()->user()) {
            return;
        }

        $page->revisionUser()->associate($user);
    }
}
