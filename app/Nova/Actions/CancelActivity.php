<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Activity;
use App\Models\States\Enrollment\Cancelled;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use App\Nova\Resources\Activity as NovaActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;

class CancelActivity extends Action
{
    use BlocksCancelledActivityRuns;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     * @var string
     */
    public $name = 'Annuleer activiteit';

    /**
     * The text to be used for the action's confirm button.
     * @var string
     */
    public $confirmButtonText = 'Annuleer activiteit';

    /**
     * The text to be used for the action's cancel button.
     * @var string
     */
    public $cancelButtonText = 'Niet annuleren';

    /**
     * The text to be used for the action's confirmation text.
     * @var string
     */
    public $confirmText = <<<'TEXT'
    Weet je zeker dat de activiteit(en) geannuleerd moet(en) worden? Dit kan niet ongedaan worden gemaakt.
    TEXT;

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

        // Skip count
        $skipCount = 0;

        // Issue the cancellation
        foreach ($models as $activity) {
            // Sanity
            \assert($activity instanceof Activity || $activity instanceof NovaActivity);

            // Skip already cancelled or ended
            if ($activity->is_cancelled || $activity->end_date < now()) {
                $skipCount++;
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

        // Result
        $totalCount = $models->count();

        // Basic results
        if ($skipCount === 0) {
            return Action::message('De activiteiten zijn geannuleerd');
        } elseif ($skipCount === $totalCount) {
            return Action::danger('Geen van de activiteiten zijn geannuleerd');
        }

        // Mixed messages
        return Action::message(sprintf(
            '%d van de %d %s activiteiten zijn geannuleerd',
            $totalCount - $skipCount,
            $totalCount
        ));
    }

    /**
     * Get the fields available on the action.
     * @return array
     */
    public function fields()
    {
        return [
            Textarea::make('Reden', 'reason')
                ->rules('required', 'max:190')
                ->rows(4)
                ->help('De reden voor annulering, max 190 tekens'),
            Boolean::make('Betalingen terugboeken', 'refund_all')
                ->help('Alle uitgevoerde betalingen terugboeken')
        ];
    }
}
