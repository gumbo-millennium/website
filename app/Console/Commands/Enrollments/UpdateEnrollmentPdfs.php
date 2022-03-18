<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Jobs\Enrollments\CreateEnrollmentTicketPdf;
use App\Models\Enrollment;
use App\Models\States\Enrollment as EnrollmentState;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Loading all ticket PDFs...');
        $allExistingFiles = Collection::make(Storage::cloud()->files('tickets'));

        $this->line('Loading all enrollments...');
        $allEnrollments = Enrollment::query()
            // Only create confirmed tickets
            ->whereState('state', [EnrollmentState\Paid::class, EnrollmentState\Confirmed::class])

            // Ensure all relations are set
            ->has('ticket')
            ->has('user')
            ->whereHas('activity', fn (Builder $query) => $query->where('end_date', '>', Date::today()->subMonth()))

            // Fetch!
            ->get(['id']);

        $allExpectedFiles = $allEnrollments->map(fn (Enrollment $enrollment) => $enrollment->pdf_path)->values();

        $this->line('Removing expired tickets...');
        foreach ($allExistingFiles as $ticketFile) {
            if (! $allExpectedFiles->contains($ticketFile)) {
                $this->line("Deleting <info>{$ticketFile}</>", null, OutputInterface::VERBOSITY_VERBOSE);
                Storage::cloud()->delete($ticketFile);
            }
        }

        $this->line('Creating missing tickets...');
        foreach ($allEnrollments as $enrollment) {
            if ($this->option('force') == true || ! $allExistingFiles->contains($enrollment->pdf_path)) {
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
            }
        }

        $this->info('All tickets updated!');

        return Command::SUCCESS;
    }
}
