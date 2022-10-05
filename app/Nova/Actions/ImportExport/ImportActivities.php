<?php

declare(strict_types=1);

namespace App\Nova\Actions\ImportExport;

use App\Excel\Imports\ActivityImport;
use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\Excel\Facades\Excel;

/**
 * An action that allows the user to upload a data sheet
 * of activities and import them into the database.
 */
class ImportActivities extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Import activities';

    public function __construct()
    {
        $this->standalone();
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $upload = Request::file('import') ?? $fields->import;
        $group = Role::whereActivityAssignable()->firstWhere('name', $fields->group);

        if (! $upload || ! $group) {
            return Action::danger(__('Please select a file and a group.'));
        }

        /** @var User $user */
        $user = Request::user();

        if (! $user->can('admin', Activity::class)
            && ! ($user->can('manage', Activity::class) && $user->hasRole($group))) {
            return Action::danger(__('You are not allowed to import activities for this group.'));
        }

        $activityCounter = 0;

        // Add a counter
        Activity::created(fn () => $activityCounter++);

        // Run the import
        Excel::import(new ActivityImport($group), $upload);

        // Report the result
        if ($activityCounter > 0) {
            return Action::message(__('Imported :count activities.', ['count' => $activityCounter]));
        }

        // Zero imports, assume failure
        return Action::danger(__('No activities were imported, maybe they already exist?'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Fields\Select::make(__('Group'), 'group')
                ->options(Role::getActivityRoles())
                ->displayUsingLabels()
                ->rules('required'),

            Fields\File::make(__('Import file'), 'import')
                ->rules([
                    'required',
                    'file',
                    'mimes:ods,xls,xlsx',
                ]),
        ];
    }
}
