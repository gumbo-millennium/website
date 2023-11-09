<?php

declare(strict_types=1);

namespace App\Events\Tenor;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GifSharedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        private string $fileId,
        private string $searchTerm,
    ) {
        // noop
    }

    /**
     * Get the ID of the file that was shared.
     */
    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }
}
