<?php

declare(strict_types=1);

namespace App\Nova\Actions\ImportExport;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\Excel\Facades\Excel;

/**
 * An action that allows the user to upload a data sheet
 * of activities and import them into the database.
 */
class ImportEnrollmentBarcodes extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $confirmButtonText = 'Start Import';

    public function __construct()
    {
        $this->standalone();
    }

    public function name()
    {
        return __('Import Barcodes');
    }

    public function getSuitableActivities(User $user): Collection
    {
        return Activity::query()
            // Never allow editing past events
            ->where('end_date', '>', Date::now())

            // Only allow editing activities that the user can manage, unless they're
            // able to admin events
            ->unless($user->can('admin', Activity::class), function ($query) use ($user) {
                $query->whereHas(
                    'roles',
                    fn ($query) => $query->whereIn('id', $user->roles->pluck('id')),
                );
            })

            // :)
            ->get();
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $upload = Request::file('import') ?? $fields->import;
        $activity = $fields->get('activity');

        /** @var User $user */
        $user = Request::user();

        if (! $user->can('admin', Activity::class)
            && ! ($user->can('manage', Activity::class) && $user->hasRole($group))) {
            return Action::danger(__('You are not allowed to import activities for this group.'));
        }

        // Run the import
        try {
            Excel::import(new EnrollmentBarcodeImport($activity), $upload);
        } catch (InvalidArgumentException $e) {
            return Action::danger(__('The uploaded file is invalid: :message.', ['message' => rtrim($e->getMessage(), '.')]));
        }

        // Zero imports, assume failure
        return Action::message(__('Import completed, your activities should now be visible.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $activities = $this->getSuitableActivities($request->user());

        $activityId = filter_var($request->resources, FILTER_VALIDATE_INT) ? $request->resources : $request->resourceId;

        return [
            Fields\Select::make(__('Activity'), 'activity')
                ->options($activities->pluck('name', 'id'))
                ->displayUsingLabels()
                ->rules([
                    'required',
                    Rule::in($activities->pluck('id')),
                ])
                ->default($activityId),

            Fields\File::make(__('Import file'), 'import')
                ->rules([
                    'required',
                    'file',
                    'mimes:ods,xls,xlsx',
                ]),
        ];
    }
}
