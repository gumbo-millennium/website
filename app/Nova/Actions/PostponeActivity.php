<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Activity;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use App\Nova\Resources\Activity as NovaActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class PostponeActivity extends Action
{
    use BlocksCancelledActivityRuns;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Stel activiteit uit';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Uitstellen';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Niet uitstellen';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = <<<'HTML'
    Stel de activiteit uit, zonder een nieuwe datum op te geven. De activiteit blijft op de activiteitenpagina
    staan totdat een nieuwe datum is gekozen via "Verplaats activiteit".<br />
    Deelnemers worden op de hoogte gebracht en blijven ingescreven.
    HTML;

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $reason = $fields->get('reason');

        // Only act on the first model
        $activity = $models->first();
        \assert($activity instanceof Activity || $activity instanceof NovaActivity);

        // Ensure it' still writeable
        if ($activity->is_cancelled || $activity->end_date < now()) {
            return Action::danger('Deze activiteit kan niet meer worden aangepast.');
        }

        // Save postpone date and reason
        $activity->postponed_at = now();
        $activity->postponed_reason = $reason;

        // Remove rescheduled flag.
        $activity->rescheduled_from = null;
        $activity->rescheduled_reason = null;

        // Store changes
        $activity->save();

        // Inform enrolled users
        // TODO

        // Report
        return Action::message('De activiteit is uitgesteld.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Reden', 'reason')
                ->rules('required')
                ->help('De reden voor uitstelling'),
        ];
    }
}
