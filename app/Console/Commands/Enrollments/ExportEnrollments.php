<?php

declare(strict_types=1);

namespace App\Console\Commands\Enrollments;

use App\Enums\ActivityExportType;
use App\Helpers\Str;
use App\Models\Activity;
use App\Services\ActivityExportService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportEnrollments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
    enrollment:export
        {activity : Activity to export, slug or ID}
        {format : Format to export into}
    CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an export for the given enrollments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ActivityExportService $service)
    {
        $activityHandle = $this->argument('activity');
        $activity = Activity::query()
            ->where(
                fn (Builder $query) => $query
                    ->where('id', $activityHandle)
                    ->orWhere('slug', $activityHandle),
            )->first();

        if (! $activity) {
            $this->error("Cannot find activity with ID or slug [{$activityHandle}]");

            return Command::FAILURE;
        }

        $exportType = Collection::make(ActivityExportType::cases())
            ->first(fn (ActivityExportType $value) => $value->name === $this->argument('format'));

        if (! $exportType) {
            $this->error("Cannot find export type [{$this->argument('format')}]");

            return Command::FAILURE;
        }

        try {
            $path = $service->createParticipantsExport($activity, $exportType);
            $this->info('Export created');
            $this->line($this->getTemporaryOrSafeUrl($path));

            return Command::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->line('Failed to create export:');
            $this->error($exception->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (! $input->getArgument('format')) {
            $types = Collection::make(ActivityExportType::cases())
                ->mapWithKeys(fn (ActivityExportType $value) => [__($value->name) => $value->name]);

            $choice = $this->choice('What format should the export be?', $types->keys()->all());

            $input->setArgument('format', $types[$choice] ?? ActivityExportType::CheckIn->name);
        }
    }

    private function getTemporaryOrSafeUrl(string $path): string
    {
        try {
            return Storage::cloud()->temporaryUrl($path, Date::now()->addHour());
        } catch (RuntimeException  $exception) {
            if (Str::containsAll($exception->getMessage(), ['driver does not support', 'temporary URLs'])) {
                return Storage::cloud()->url($path);
            }

            $this->line("Failed to create URL to file [{$path}]:");
            $this->error($exception->getMessage());
        }
    }
}
