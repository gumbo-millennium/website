<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Exports\ActivityParticipantsExport;
use App\Exports\ActivityParticipantsMedicalExport;
use App\Helpers\Str;
use App\Models\Activity;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

class ExportActivityParticipants extends Action
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
    public function fields()
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
        // Fetch data
        $type = $fields->get('type', self::TYPE_CHECK_IN);
        $activity = $models->first();

        // Check if a file already exists and don't override it if it does
        $filePath = "activities/{$activity->slug}/deelnemers.${type}.ods";
        $fileDisk = Storage::cloud();
        if ($fileDisk->missing($filePath) || Date::now()->diffInMinutes(Date::createFromTimestamp($fileDisk->lastModified($filePath))) > 15) {
            // Get CSV from the proper type
            $exportModel = match ($type) {
                self::TYPE_ARCHIVE => $this->getArchiveCsv($activity),
                self::TYPE_CHECK_IN => $this->getCheckInCsv($activity),
            };

            // Build CSV and store on cloud
            $writeOkay = ExcelFacade::store($exportModel, $filePath, Config::get('filesystems.cloud'), Excel::ODS, [
                'visibility' => 'private',
            ]);

            if (! $writeOkay) {
                return $this->danger(__('Failed to export participants: :reason', [
                    'reason' => __('Creating or writing of sheet failed.'),
                ]));
            }
        }

        try {
            $downloadUrl = $fileDisk->temporaryUrl($filePath, Date::now()->addMinutes(5));
        } catch (RuntimeException $exception) {
            if (
                App::isProduction()
                || ! Str::containsAll($exception->getMessage(), ['driver does not support', 'temporary URLs'])
            ) {
                return $this->danger(__('Failed to export participants: :reason', [
                    'reason' => __($exception->getMessage()),
                ]));
            }

            $downloadUrl = $fileDisk->url($filePath);
        }

        $fileType = array_map('__', self::TYPE_LABEL_MAPPING)[$type] ?? $type;

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
        return new ActivityParticipantsMedicalExport($activity);
    }

    /**
     * Builds a simple list to check users in, contains first and last name
     * and e-mail address, as well as state, ticket and ticket price.
     */
    private function getCheckInCsv(Activity $activity): ActivityParticipantsExport
    {
        return new ActivityParticipantsExport($activity);
    }
}
