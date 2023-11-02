<?php

declare(strict_types=1);

namespace App\Listeners\Images;

use App\Events\Images\ImageDeleted;
use App\Jobs\Images\DeleteSizedImagesJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ImageDeletionListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param App\Events\Images\ImageDeleted $event
     * @return void
     */
    public function handle(ImageDeleted $event)
    {
        DeleteSizedImagesJob::dispatch($event->getImageProperty());
    }
}
