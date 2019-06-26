<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Fields\Select;
use App\Models\User;

/**
 * Handles join submission completions, which have either a positive or negative
 * response.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class HandleJoinSubmission extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Return if no results
        if ($models->isEmpty()) {
            return;
        }

        // Check accepted state
        $accepted = $fields->result === 'yes';

        // Counts
        $failCount = 0;
        $updatedCount = 0;
        $totalCount = $models->count();

        foreach ($models as $model) {
            // Skip if the request wasn't handled yet.
            if ($model->granted !== null) {
                $this->markAsFailed($model, 'Request was already handled');
                continue;
            }

            // Add granted flag
            $model->granted = $accepted;
            $model->save(['granted']);

            // Mark as completed
            $this->markAsFinished($model);

            // Find user with the same email address
            $user = User::where(['email' => $model->email])->first();

            // Continue if none are present, or assign member role if one is present.
            if (!$user) {
                continue;
            } elseif ($accepted) {
                $user->assignRole('member');
            }
        }

        if ($totalCount === 1) {
            if ($failCount) {
                return Action::danger('Aanvraag afwerken mislukt.');
            }

            return Action::message('Aanvraag afgewerkt.');
        }

        if ($failCount == $totalCount) {
            return Action::danger(sprintf('Alle %d aanvragen konden niet worden afgewerkt', $failCount));
        } elseif ($failCount === 0) {
            return Action::message(sprintf('Alle %d aanvragen zijn afgewerkt', $totalCount));
        }

        $labelType = $failCount > $totalCount / 2 ? 'danger' : 'message';
        return Action::$labelType(sprintf('%d van de %d aanvragen afgewerkt', $totalCount - $failCount, $failCount));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Resultaat', 'result')
                ->options([
                    'yes' => 'Goedgekeurd',
                    'no' => 'Afgewezen',
                ]),
        ];
    }
}
