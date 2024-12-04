<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Jobs\Enrollments\CreateEnrollmentTicketPdf;
use App\Models\Enrollment;
use App\Models\States\Enrollment as EnrollmentState;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Laravel\Prompts\Progress;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateEnrollmentPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        gumbo:update-ticket-pdfs
            {--force : Update all existing tickets too}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update PDFs for all enrollments, removing those that have expired.';

    private FilesystemAdapter $disk;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Getting disk');
        $this->disk = Storage::disk(Enrollment::first()->pdf_disk);

        $this->line('Loading all ticket PDFs...');
        $allExistingFiles = Collection::make($this->disk->allFiles(Enrollment::PDF_TICKET_DIR));

        $this->line('Loading all enrollments...');
        $allEnrollments = Enrollment::query()
            // Only create confirmed tickets
            ->whereState('state', [EnrollmentState\Paid::class, EnrollmentState\Confirmed::class])

            // Ensure all relations are set
            ->has('ticket')
            ->has('user')
            ->whereHas('activity', fn (Builder $query) => $query->where('end_date', '>', Date::today()->subYear()))

            // Fetch!
            ->get(['id', 'user_id']);

        $allExpectedFiles = $allEnrollments->pluck('pdf_path')->values();

        $this->cleanupExistingFiles($allExistingFiles, $allExpectedFiles);

        $this->createOrUpdateTickets($allEnrollments, $allExistingFiles);

        return self::SUCCESS;
    }

    private function cleanupExistingFiles(Collection $allExistingFiles, Collection $allExpectedFiles): void
    {
        if ($allExistingFiles->isEmpty()) {
            $this->line('No expired tickets to remove');

            return;
        }

        $progress = new Progress('Removing expired tickets...', $allExistingFiles);
        $progress->start();

        foreach ($allExistingFiles as $ticketFile) {
            if (! $allExpectedFiles->contains($ticketFile)) {
                $this->line("Deleting <info>{$ticketFile}</>", null, OutputInterface::VERBOSITY_VERBOSE);
                $this->disk->delete($ticketFile);
            }

            $progress->advance();
        }

        $progress->finish();
    }

    private function createOrUpdateTickets(Collection $allEnrollments, Collection $allExistingFiles): void
    {
        if ($allEnrollments->isEmpty()) {
            $this->line('No enrollments to check');

            return;
        }

        $this->line("{$allExistingFiles->count()} bestaat al");

        $progress = new Progress('Creating missing tickets...', $allEnrollments);
        $progress->start();

        foreach ($allEnrollments as $enrollment) {
            if (! $this->option('force') && $allExistingFiles->contains($enrollment->pdf_path)) {
                $progress->advance();

                continue;
            }

            $this->line("Requesting <info>{$enrollment->pdf_path}</>", null, OutputInterface::VERBOSITY_VERBOSE);

            try {
                CreateEnrollmentTicketPdf::dispatch(
                    Enrollment::find($enrollment->id),
                );
                $this->line('Generated OK', null, OutputInterface::VERBOSITY_VERY_VERBOSE);
            } catch (Exception $exception) {
                $this->line("<error>FAIL</>: Generation for <info>{$enrollment->id}</> ({$enrollment->user?->name} at {$enrollment->activity?->name}) failed:");
                $this->line("      {$exception->getMessage()}");
                $this->line("      {$exception->getFile()}:{$exception->getLine()}");
            }

            $progress->advance();
        }

        $progress->finish();

        $this->info('All tickets updated!');
    }
}
