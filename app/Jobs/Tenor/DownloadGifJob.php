<?php

declare(strict_types=1);

namespace App\Jobs\Tenor;

use App\Services\TenorGifService;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DownloadGifJob extends TenorJob
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $group,
        private readonly string $fileId,
        private readonly string $fileUrl,
    ) {
        // noop
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TenorGifService $gifService)
    {
        // Download the gif
        $response = Http::get($this->fileUrl, [
            'Content-Type' => 'image/gif',
        ]);

        // Fail if, well, failed
        if (! $response->successful()) {
            throw new RuntimeException('Failed to download gif');
        }

        // Store the body somehwere
        $tempFile = tempnam(sys_get_temp_dir(), 'tenor');

        try {
            // Write the file to disk
            file_put_contents($tempFile, $response->body());

            // Write the file to cloud
            $gifService->putGifInGroup($this->group, new File($tempFile), $this->fileId);
        } finally {
            // Delete the temporary file
            @unlink($tempFile);
        }
    }
}
