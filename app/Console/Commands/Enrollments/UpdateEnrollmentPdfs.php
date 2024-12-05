<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Jobs\Enrollments\CreateEnrollmentTicketPdf;
use App\Models\Enrollment;
use App\Models\States\Enrollment as EnrollmentState;
use Exception;
use Illuminate\Console\Command;
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
            {--recent : Only update upcoming tickets}
            {--sync : Run in sync, not using the queue}
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
        $allEnrollments = $this->getEnrollments();

        $allExpectedFiles = $allEnrollments->pluck('pdf_path')->values();

        if ($this->option('recent')) {
            $this->line('Not cleaning up, scope is narrowed down', null, OutputInterface::VERBOSITY_NORMAL);
        } else {
            $this->cleanupExistingFiles($allExistingFiles, $allExpectedFiles);
        }

        $this->createOrUpdateTickets($allEnrollments, $allExistingFiles);

        return self::SUCCESS;
    }

    /**
     * Select eligible tickets.
     */
    private function getEnrollments(): Collection
    {
        return Enrollment::query()
            // Only create confirmed tickets
            ->whereState('state', [EnrollmentState\Paid::class, EnrollmentState\Confirmed::class])

            // Timebox, if requested
            ->when(
                $this->option('recent'),
                fn ($q) => $q->whereHas(
                    'activity',
                    fn ($q2) => $q2->where('end_date', '>', Date::now()),
                ),
            )

            // Ensure all relations are set
            ->has('ticket')
            ->has('user')
            ->has('activity')

            // Fetch all data required for pdf_path generation
            ->get(['id', 'user_id']);
    }

    /**
     * Remove files we don't need.
     */
    private function cleanupExistingFiles(Collection $allExistingFiles, Collection $allExpectedFiles): void
    {
        if ($allExistingFiles->isEmpty()) {
            $this->line('No tickets exist yet.');

            return;
        }

        $ticketsToRemove = collect($allExistingFiles)
            ->reject(fn ($row) => $allExpectedFiles->contains($row));

        if ($ticketsToRemove->isEmpty()) {
            $this->line('No tickets to remove.');

            return;
        }

        $progress = new Progress('Removing tickets...', $ticketsToRemove);
        $progress->start();

        $ticketsToRemove->chunk(10)
            ->each(function ($files) use ($progress) {
                $files->each(fn ($file) => $this->line("Deleting <info>{$file}</>", null, OutputInterface::VERBOSITY_VERBOSE));

                $this->disk->delete($files);

                $progress->advance($files->count());
            });

        $progress->finish();
    }

    private function createOrUpdateTickets(Collection $allEnrollments, Collection $allExistingFiles): void
    {
        if ($allEnrollments->isEmpty()) {
            $this->line('No enrollments to check');

            return;
        }

        $ticketsToCreate = $allEnrollments;
        if ($this->option('force')) {
            $this->line('<fg=red>--force given, recreating everything</>');
        } else {
            $ticketsToCreate = $ticketsToCreate
                ->reject(fn ($enrollment) => $allExistingFiles->contains($enrollment->pdf_path));
        }

        if ($ticketsToCreate->isEmpty()) {
            $this->line('No tickets to create');

            return;
        }

        if (! $this->option('sync')) {
            $this->withProgressBar($ticketsToCreate, fn ($enrollment) => CreateEnrollmentTicketPdf::dispatch(Enrollment::find($enrollment->id)));

            $this->info('Ticket updates queued');

            return;
        }

        $progress = new Progress('Creating missing tickets...', $ticketsToCreate);
        $progress->start();

        foreach ($ticketsToCreate as $enrollment) {
            $this->line("Requesting <info>{$enrollment->pdf_path}</>", null, OutputInterface::VERBOSITY_VERBOSE);

            try {
                CreateEnrollmentTicketPdf::dispatchSync(
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
