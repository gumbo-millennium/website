<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\JoinSubmission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

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
            if (!$this->handleSubmission($model, $accepted)) {
                continue;
            }

            $updatedCount++;
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
        $submission->save(['granted', 'updated_at']);

        // Mark as completed
        $this->markAsFinished($submission);

        // Find user with the same verified email address
        $user = User::query()
            ->where('email', $submission->email)
            ->whereNotNull('email_verified_at')
            ->first();

        // Flag as accepted if a user was found and the request was granted
        if ($user && $accepted) {
            $user->assignRole('member');
        }

        // Return ok
        return true;
    }
}
