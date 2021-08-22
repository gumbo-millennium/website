<?php

declare(strict_types=1);

namespace App\Jobs\Avg;

use App\Models\DataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(DataExport $export)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatchNow(DataExport $export)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatchAfterResponse(DataExport $export)
 */
class CreateUserDataExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private DataExport $export;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DataExport $export)
    {
        $this->export = $export;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if eligible
        $export = $this->export;
        if ($export->is_expired || $export->path !== null) {
            return;
        }

        // Get user
        $user = $export->user;
        $user->loadMissing([
            'enrollments',
            'enrollments.activity:name,slug',
            'downloads',
            'files:id,title,slug',
            'files.category:title,slug',
            'orders',
            'orders.variants:name,slug',
        ]);

        // Prep data
        $data = [
            'user-data' => $export->user->withoutRelations()->toArray(),
            'activity-enrollments' => $export->user->enrollments->toArray(),
            'file-downloads' => $export->user->downloads->map(fn ($download) => $download->withoutRelations()->toArray()),
            'file-uploads' => $export->user->files->map(fn ($file) => $file->withoutRelations()->toArray()),
            'shop-orders' => $export->user->orders->map(fn ($order) => array_merge(
                $order->withoutRelations()->toArray(),
                ['products' => $order->variants->map(fn ($variant) => $variant->withoutRelations()->toArray())->toArray()],
            )),
        ];

        // TODO: Implement handle() method.

        // Get file
        $zipFile = tempnam(sys_get_temp_dir(), 'zip');

        // Create zip
        $zip = new ZipArchive();
        $zip->open($zipFile, ZipArchive::OVERWRITE);
        $zip->addFromString('data.json', json_encode($data, JSON_PRETTY_PRINT));
        $zip->close();

        // Save file
        $exportPath = Storage::putFile('user-exports', new File($zipFile));

        // Delete the source file
        unlink($zipFile);

        // Refresh export
        $export = $this->export->refresh();
        if ($export->path !== null) {
            Storage::delete($exportPath);

            return;
        }

        // Assign to export
        $this->export->path = $exportPath;

        // Update dates
        $this->export->completed_at = Date::now();
        $this->export->expires_at = Date::now()->addDays(Config::get('gumbo.export-expire-days'));

        // Save
        $this->export->save();
    }
}
