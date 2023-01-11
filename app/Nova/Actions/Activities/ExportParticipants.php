<?php

declare(strict_types=1);

namespace App\Nova\Actions\Activities;

use App\Enums\ActivityExportType;
use App\Excel\Exports\ActivityParticipantsExport;
use App\Excel\Exports\ActivityParticipantsFullExport;
use App\Excel\Exports\ActivityParticipantsPresenceList;
use App\Helpers\Str;
use App\Models\Activity;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use App\Services\ActivityExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use RuntimeException;

class ExportParticipants extends Action
{
    use BlocksCancelledActivityRuns;

    public const TYPE_ARCHIVE = 'archive';

    public const TYPE_CHECK_IN = 'check-in';

    private const TYPE_LABEL_MAPPING = [
        self::TYPE_CHECK_IN => 'Check-in list',
        self::TYPE_ARCHIVE => 'Full list (with medical data)',
    ];

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Export participants';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = <<<'DOC'
    Create an export of the participants of this activity.
    DOC;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Exporteer gegevens';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make('Type export', 'type')
                ->rules('required', 'max:190')
                ->options(array_map('__', self::TYPE_LABEL_MAPPING))
                ->default(fn () => self::TYPE_CHECK_IN),
        ];
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Determine type
        $exportType = $fields->get('type', self::TYPE_CHECK_IN);
        $wantedType = match ($exportType) {
            self::TYPE_CHECK_IN => ActivityExportType::CheckIn,
            self::TYPE_ARCHIVE => ActivityExportType::Full,
            default => ActivityExportType::CheckIn,
        };

        // Get activity
        $activity = $models->first();

        /** @var ActivityExportService $exportService */
        $exportService = App::make(ActivityExportService::class);

        // Write export
        try {
            $exportPath = $exportService->createParticipantsExport($activity, $wantedType);
        } catch (RuntimeException $exception) {
            return Action::danger(__('Creating or writing of sheet failed.'));
        }

        try {
            $downloadUrl = Storage::cloud()->temporaryUrl($exportPath, Date::now()->addMinutes(5));
        } catch (RuntimeException $exception) {
            if (
                App::isProduction()
                || ! Str::containsAll($exception->getMessage(), ['driver does not support', 'temporary URLs'])
            ) {
                return $this->danger(__('Failed to export participants: :reason', [
                    'reason' => __($exception->getMessage()),
                ]));
            }

            $downloadUrl = Storage::cloud()->url($exportPath);
        }

        $fileType = __(self::TYPE_LABEL_MAPPING[$exportType]) ?? $exportType;

        return $this->download($downloadUrl, "Deelnemers {$activity->name} ({$fileType}).ods");
    }

    /**
     * Ensure only users who can manage this activity can export participants.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model)
    {
        if ($request->user()?->can('manage', $model) !== true) {
            return false;
        }

        return parent::authorizedToRun($request, $model);
    }

    private function getArchiveCsv(Activity $activity): ActivityParticipantsExport
    {
        return new ActivityParticipantsFullExport($activity);
    }

    /**
     * Builds a simple list to check users in, contains first and last name
     * and e-mail address, as well as state, ticket and ticket price.
     */
    private function getCheckInCsv(Activity $activity): ActivityParticipantsExport
    {
        return new ActivityParticipantsPresenceList($activity);
    }
}
