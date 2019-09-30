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
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        $updatedCount = 0;
        $totalCount = $models->count();

        // Iterate through each model
        foreach ($models as $model) {
            if ($this->handleSubmission($model, $accepted)) {
                $updatedCount++;
            }
        }

        $failCount = $totalCount - $updatedCount;

        // Total success
        if ($failCount === 0) {
            return Action::message(sprintf('Alle %d aanvragen zijn afgewerkt', $totalCount));
        }

        // Partial failure
        $labelType = $failCount > $updatedCount / 2 ? 'danger' : 'message';
        return Action::$labelType(sprintf('%d van de %d aanvragen afgewerkt', $updatedCount, $failCount));
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

    /**
     * Handles a single submission
     *
     * @param JoinSubmission $submission
     * @param bool $accepted
     * @return bool
     */
    private function handleSubmission(JoinSubmission $submission, bool $accepted): bool
    {
        // Skip if the request wasn't handled yet.
        if ($submission->granted !== null) {
            $this->markAsFailed($submission, 'Request was already handled');
            return true;
        }

        // Add granted flag
        $submission->granted = $accepted;
        $submission->save(['granted']);

        // Mark as completed
        $this->markAsFinished($submission);

        // Find user with the same email address
        $user = User::where(['email' => $submission->email])->first();

        // Report as failed when no user could be found
        if (!$user) {
            return false;
        }

        // Flag as accepted
        if ($accepted) {
            $user->assignRole('member');
        }

        // Return ok
        return true;
    }
}
