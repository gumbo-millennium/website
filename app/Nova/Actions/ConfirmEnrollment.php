<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Enrollment;
use App\Models\States\Enrollment\Confirmed;
use App\Models\States\Enrollment\Paid;
use App\Models\User;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\EnrollmentPaid;
use App\Nova\Resources\Enrollment as NovaEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

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
    public $name = "Bevestig\u{a0}inschrijving"; // contains non-breaking space (U+00A0)

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Inschrijving bevestigen';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Annuleren';

    /**
     * Makes a new Confirm Enrollment configured to this model.
     *
     * @return ConfirmEnrollment
     */
    public static function make(Enrollment $enrollment, User $user): self
    {
        // Prep proper text
        $noticeText = 'Weet je zeker dat je deze inschrijving wilt bevestigen?';
        if ($enrollment->requires_payment) {
            $noticeText .= ' Dit verwijderd de betaalverplichting.';
        }

        // Make instance
        return (new self())
            ->canSee(static fn () => \in_array(Confirmed::$name, $enrollment->state->transitionableStates(), true))
            ->canRun(static fn () => $user->can('manage', $enrollment))
            ->confirmText($noticeText)
            ->onlyOnTableRow();
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return Action::danger('This can only be used with one enrollment at a time');
        }

        // Get the model
        $model = $models->first();

        // Get the inner enrollment
        if ($model instanceof NovaEnrollment) {
            $model = $model->model();
        }

        // Check type
        \assert($model instanceof Enrollment);
        $model->loadMissing('user:id,name');

        // Get user name
        $userName = $model->user->name;

        // Skip if not confirm-able
        $options = $model->state->transitionableStates();
        if (! \in_array(Confirmed::$name, $options, true)) {
            return Action::danger("De inschrijving van {$userName} kan niet worden bevestigd.");
        }

        // Check if payment is required
        $needsPayment = $model->requires_payment;
        $notice = null;

        // Mark paid if required
        if ($needsPayment) {
            // Transition to paid
            $model->state->transitionTo(Paid::class);

            // Prep notice
            $notice = new EnrollmentPaid($model);
        } else {
            // Transition to confirmed
            $model->state->transitionTo(Confirmed::class);

            // Prep notice
            $notice = new EnrollmentConfirmed($model);
        }

        // Save the model
        $model->save();

        // Send the notification
        $model->user->notify($notice);

        // Done ☺
        $result = $needsPayment ? 'gemarkered als betaald' : 'bevestigd';

        return Action::message("De inschrijving van {$userName} is {$result}");
    }
}
