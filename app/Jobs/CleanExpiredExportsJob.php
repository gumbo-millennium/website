<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\FileExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanExpiredExportsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var iterable<FileExport> $targets */
        $targets = FileExport::query()
            ->wherePurgeable()
            ->get();

        foreach ($targets as $file) {
            if (!$file->is_valid_export) {
                $file->delete();
                continue;
            }

            if (!Storage::exists($file->path)) {
                $file->delete();
                continue;
            }

            Storage::delete($file->path);

            $file->delete();
        }
    }
}
