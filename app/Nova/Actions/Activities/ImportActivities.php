<?php

declare(strict_types=1);

namespace App\Nova\Actions\Activities;

use App\Excel\Imports\ActivityImport;
use App\Models\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\FormData;
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

    public $confirmButtonText = 'Start';

    public function name()
    {
        return __('Import Activities');
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($fields->get('action') === 'download') {
            return Action::download(route('admin.activity.import-template'), 'Import Template.xlsx');
        }

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
        $hideIfDownloading = function (array $rules) {
            return function (Fields\Field $field, NovaRequest $novaRequest, FormData $formData) use ($rules) {
                if ($formData->action === 'export') {
                    $field->hide();
                } else {
                    $field->rules($rules);
                }
            };
        };

        return [
            Fields\Select::make(__('Action'), 'action')
                ->options([
                    'download' => __('Import Activities'),
                    'import' => __('Download Template'),
                ])
                ->rules([
                    'required',
                    Rule::in(['download', 'import']),
                ]),

            Fields\Select::make(__('Group'), 'group')
                ->options(Role::getActivityRoles())
                ->displayUsingLabels()
                ->dependsOn(['action'], $hideIfDownloading(['required'])),

            Fields\File::make(__('Import File'), 'import')
                ->dependsOn(['action'], $hideIfDownloading([
                    'required',
                    'file',
                    'mimes:ods,xls,xlsx',
                ])),
        ];
    }
}
