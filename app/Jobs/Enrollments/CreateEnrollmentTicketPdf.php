<?php

declare(strict_types=1);

namespace App\Jobs\Enrollments;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class CreateEnrollmentTicketPdf implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Enrollment $enrollment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $enrollment = $this->enrollment->loadMissing([
            'activity',
            'ticket',
            'user',
        ]);

        Pdf::view('pdf.ticket', [
            'enrollment' => $enrollment,
            'ticket' => $enrollment->ticket,
            'activity' => $enrollment->activity,
            'subject' => $enrollment->user,
        ])
            ->format(Format::A4)
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->scale(0.8);
                $browsershot->pages('1');
            })
            ->disk($enrollment->pdf_disk)
            ->save($enrollment->pdf_path);
    }
}
