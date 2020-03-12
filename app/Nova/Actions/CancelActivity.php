<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Activity;
use App\Models\States\Enrollment\Cancelled;
use App\Nova\Resources\Activity as NovaActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;

class CancelActivity extends DestructiveAction
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     * @var string
     */
    public $name = 'Annuleer activiteit';

    /**
     * Perform the action on the given models.
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $cancelReason = $fields->get('reason');
        $cancelRefund = (bool) $fields->get('refund_all');

        // Issue the cancellation
        foreach ($models as $activity) {
            // Sanity
            \assert($activity instanceof Activity || $activity instanceof NovaActivity);

            // Skip already cancelled
            if ($activity->is_cancelled) {
                continue;
            }

            // Save cancellation
            $activity->cancelled_at = now();
            $activity->cancelled_reason = $cancelReason;
            $activity->save();

            // Refund all, if requested
            if ($cancelRefund) {
                foreach ($activity->enrollments as $enrollment) {
                    // Transition to cancellation
                    $enrollment->state->transitionTo(Cancelled::class);

                    // Save the enrollment
                    $enrollment->save();
                }
            }
        }

        // Action
        return Action::message(sprintf(
            'De %s geannuleerd',
            $models->count() != 1 ? 'activiteiten zijn' : 'activiteit is'
        ));
    }

    /**
     * Get the fields available on the action.
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Reden', 'reason')
                ->rules('required')
                ->help('De reden voor annulering'),
            Boolean::make('Betalingen terugboeken', 'refund_all')
                ->help('Alle uitgevoerde betalingen terugboeken')
        ];
    }
}
