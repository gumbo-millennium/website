<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Nova\Resources\Enrollment as NovaEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

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
    public $name = 'Uitschrijven';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $skipCount = 0;
        $cancelCount = 0;
        foreach ($models as $model) {
            if (!$model instanceof Enrollment && !$model instanceof NovaEnrollment) {
                $skipCount++;
                continue;
            }

            // Flag deletion reason
            $model->deleted_reason = (string) $fields->reason;

            // Transition to cancellation
            $model->state->transitionTo(Cancelled::class);

            // Save the model
            $model->save();

            // Add count
            $cancelCount++;
        }

        $totalCount = $skipCount + $cancelCount;

        if ($cancelCount === 1 && $skipCount === 0) {
            return Action::message("De inschrijving van {$model->user->name} is geannuleerd");
        }

        if ($cancelCount < $skipCount) {
            return Action::danger("Slechts {$cancelCount} van de {$totalCount} inschrijvingen zijn geannuleerd");
        }

        return Action::danger("{$cancelCount} van de {$totalCount} inschrijvingen zijn geannuleerd");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Reden', 'reason')
                ->options([
                    StripeServiceContract::REFUND_REQUESTED_BY_CUSTOMER => 'Aangevraagd door gebruiker',
                    StripeServiceContract::REFUND_DUPLICATE => 'Duplicaat',
                    StripeServiceContract::REFUND_FRAUDULENT => 'Frauduleus',
                ]),
        ];
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param Request $request
     * @param Enrollment $model
     * @return bool
     */
    public function authorizedToRun(Request $request, $model)
    {
        return $request->user()->can('manage', $model->activity);
    }
}
