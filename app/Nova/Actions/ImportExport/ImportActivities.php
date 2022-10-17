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
class ImportActivities extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $confirmButtonText = 'Start import';

    public function __construct()
    {
        $this->standalone();
    }

    public function name()
    {
        return __('Import items');
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

        // Run the import
        try {
            Excel::import(new ActivityImport($group), $upload);
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
