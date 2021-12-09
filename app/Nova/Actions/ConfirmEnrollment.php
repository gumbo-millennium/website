<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

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
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Confirm Enrollment';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Cancel';

    /**
     * Makes a new Confirm Enrollment configured to this model.
     *
     * @return ConfirmEnrollment
     */
    public static function make(...$arguments): self
    {
        // Validate arguments
        throw_unless(count($arguments) === 2, new InvalidArgumentException('ConfirmEnrollment::make requires two arguments.'));

        // Validate types
        [$enrollment, $user] = $arguments;
        throw_unless($enrollment instanceof Enrollment, new InvalidArgumentException('First argument must be an Enrollment.'));
        throw_unless($user instanceof User, new InvalidArgumentException('Second argument must be a User.'));

        // Prep proper text
        $noticeText = [__('Are you sure you wish to confirm this enrollment?')];
        if ($enrollment->price > 0) {
            $noticeText[] = __('This will bypass the payment requirement.');
        }
        $noticeText = implode(' ', $noticeText);

        // Make instance
        return (new self())
            ->canSee(static fn () => in_array(States\Confirmed::$name, $enrollment->state->transitionableStates(), true))
            ->canRun(static fn () => $user->can('manage', $enrollment))
            ->confirmText($noticeText)
            ->onlyOnTableRow();
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
            $model->transitionTo(States\Seeded::class);
        }

        // Transition from seeded → confirmed
        if ($model->state instanceof States\Seeded) {
            $model->transitionTo(States\Confirmed::class);
        }

        // Save the model
        $model->save();

        return Action::message(__('Enrollment confirmed'));
    }
}
