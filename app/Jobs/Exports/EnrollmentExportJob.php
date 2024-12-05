<?php

declare(strict_types=1);

namespace App\Jobs\Exports;

use App\Models\Enrollment;
use App\Models\ModelExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnrollmentExportJob extends ExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Enrollment $enrollment)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $enrollment = $this->enrollment;
        $enrollment->loadMissing(['activity', 'user', 'ticket']);

        $attachment = ModelExport::forModel($enrollment)->first();
        $attachment->user()->associate($enrollment->user);
    }
}
