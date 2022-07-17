<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\Enrollments\CancelEnrollmentJob;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class CancelEnrollment extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Cancel Enrollment';

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $count = 0;
        foreach ($models as $model) {
            CancelEnrollmentJob::dispatch($model, true);

            $count++;
        }

        return Action::message(__('Requested cancellation of :count enrollment(s)', [
            'count' => $count,
        ]));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param Enrollment $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model)
    {
        return $request->user()->can('manage', $model->activity);
    }
}
