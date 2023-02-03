<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @method static static make(Enrollment $enrollment, User $user)
 */
class ConfirmEnrollment extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Confirm Enrollment'; // contains non-breaking space (U+00A0)

    /**
     * Makes a new Confirm Enrollment configured to this model.
     */
    public function __construct()
    {
        $this
            // The 'confirmation' is the body
            ->confirmText(implode(PHP_EOL, [
                __('Are you sure you wish to confirm this enrollment?'),
                __('You may optionally choose to also bypass the payment requirement, but doing so is not recommended without prior authorization.'),
            ]))

            // The buttons
            ->confirmButtonText(__('Apply'))
            ->cancelButtonText(__('Cancel'))

            // And make sure it's only on the detail row
            ->onlyOnDetail();
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return Action::danger(__('This action can only be used with one enrollment at a time'));
        }

        // Get the model
        $model = $models->first();

        // Fail if cancelled
        if ($model->state instanceof States\Cancelled) {
            return Action::danger(__('This enrollment has been cancelled'));
        }

        // Fail if already cancelled
        if ($model->state instanceof States\Confirmed) {
            return Action::danger(__('This enrollment has already been confirmed'));
        }

        // Transition from created → seeded
        if ($model->state instanceof States\Created) {
            $model->state->transitionTo(States\Seeded::class);
        }

        // Transition from seeded → confirmed
        if ($model->state instanceof States\Seeded) {
            $model->state->transitionTo(States\Confirmed::class);
        }

        // Save the model
        $model->save();

        return Action::message(__('Enrollment confirmed'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        $enrollment = $request->findModel($request->resourceId ?? $request->resources);

        // Match `null` and `0`
        if (! $enrollment?->total_price) {
            return [];
        }

        return [
            Fields\Boolean::make('Bypass Payment', 'bypass_payment')
                ->help(__('Bypassing the payment requirement is not recommended without prior authorization.')),
        ];
    }

    public function authorizedToSee(Request $request)
    {
        $model = $request->findModel($request->resourceId ?? $request->resources);

        return $model && $request->user()->can('manage', $model);
    }

    /**
     * Determine if the action is authorized to run.
     * @param Enrollment $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model)
    {
        return $request->user()->can('manage', $model) && ! $model->is_stable;
    }
}
