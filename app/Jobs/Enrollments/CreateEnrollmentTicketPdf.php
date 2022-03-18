<?php

declare(strict_types=1);

namespace App\Jobs\Enrollments;

use App\Helpers\Str;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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

        Config::set('dompdf.debugCss', true);

        $pdf = Pdf::loadView('pdf.ticket', [
            'enrollment' => $enrollment,
            'ticket' => $enrollment->ticket,
            'activity' => $enrollment->activity,
            'subject' => $enrollment->user,
        ])->addInfo([
            'Author' => 'Gumbo Millennium',
            'Title' => sprintf('Ticket voor %s van %s', Str::ascii($enrollment->activity->name), Str::ascii($enrollment->user->name)),
        ]);

        Storage::cloud()->put($enrollment->pdf_path, $pdf->output());
    }
}
