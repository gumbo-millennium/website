<?php

declare(strict_types=1);

namespace App\Nova\Actions\Activities;

use App\Excel\Imports\EnrollmentBarcodeImport;
use App\Helpers\Str;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
 * Action that allows the user to export the participants
 * to re-assign barcodes, or to import a new list of barcodes for
 * each participant.
 */
class ReplaceBarcodes extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use Traits\BlocksCancelledActivityRuns;

    public $confirmButtonText = 'Start';

    public function __construct()
    {
        $this->confirmText = implode("\n\n", [
            __('Select ":download" to download a list of all enrollments. You can then modify the barcode type and actual barcode to replace the barcode on the enrollment.', [
                'download' => __('Download Transfer Template'),
            ]),
            __('Leave entries empty (or remove them) to skip updating them. Their existing barcode will be retained.'),
            __('Note that enrollments with custom barcodes will not have their barcodes rotated in case of a transfer. This may allow for barcode reuse.'),
        ]);
    }

    public function name()
    {
        return __('Replace Barcodes');
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
                    'role',
                    fn ($query) => $query->whereIn('id', $user->roles->pluck('id')),
                );
            })

            // ☺
            ->get();
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return Action::danger(__('You can only replace barcodes for one activity at a time.'));
        }

        /** @var Activity */
        $activity = $models->first();

        /** @var User */
        $user = Request::user();

        if ($fields->get('action') === 'download') {
            $filename = sprintf('%s.xlsx', __('Barcodes enrollments :name', ['name' => Str::of($activity->name)->ascii()]));

            Log::info('User {user} is downloading a barcode transfer template for activity {activity}', [
                'user' => $user->id,
                'activity' => $activity->id,
            ]);

            return Action::download(route('admin.activity.replace-barcodes-template', $activity), $filename);
        }

        $upload = Request::file('import') ?? $fields->import;

        /** @var User */
        $user = Request::user();

        // Require the user to be able to manage the activity
        if (! $user->can('manage', $activity)) {
            return Action::danger(__('You are not allowed to import activities for this group.'));
        }

        try {
            // Map the import to a collection
            $result = Excel::toCollection(new EnrollmentBarcodeImport($activity), $upload);

            // Save all nodes, as a transaction
            DB::transaction(fn () => $result->each->save());

            // Done :)
            return Action::message(__('Succesfully updated the barcode of :count participants.', ['count' => $result->count()]));
        } catch (InvalidArgumentException $e) {
            // Oh no, fucky wucky
            return Action::danger(__('The uploaded file is invalid: :message.', ['message' => rtrim($e->getMessage(), '.')]));
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Fields\Select::make(__('Action'), 'action')
                ->options([
                    'download' => __('Download Transfer Template'),
                    'import' => __('Import Filled-in Template'),
                ])
                ->rules([
                    'required',
                    Rule::in(['import', 'download']),
                ]),

            Fields\File::make(__('Import File'), 'import')
                ->dependsOn(['action'], function (Fields\Field $field, NovaRequest $request, FormData $formData) {
                    if ($formData->action === 'download') {
                        $field->hide();
                    } else {
                        $field->rules([
                            'required',
                            'file',
                            'mimes:ods,xls,xlsx',
                        ]);
                    }
                }),
        ];
    }
}
