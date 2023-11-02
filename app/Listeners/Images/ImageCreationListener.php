<?php

declare(strict_types=1);

namespace App\Listeners\Images;

use App\Events\Images\ImageCreated;
use App\Jobs\Images\CreateSizedImagesJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ImageCreationListener implements ShouldQueue
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
     * @param App\Events\Images\ImageCreated $event
     * @return void
     */
    public function handle(ImageCreated $event)
    {
        CreateSizedImagesJob::dispatch($event->getImageProperty());
    }
}
