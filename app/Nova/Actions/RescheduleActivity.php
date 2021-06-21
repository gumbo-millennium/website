<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Activity;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use App\Nova\Resources\Activity as NovaActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;

class RescheduleActivity extends Action
{
    use BlocksCancelledActivityRuns;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Verplaats activiteit';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Verplaatsen';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Niet verplaatsen';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = <<<'TEXT'
    Verplaats de activiteit naar een nieuwe, al bekende datum. De deelnemers zullen per mail automatisch
    op de hoogte worden gebracht en blijven ingeschreven op de activiteit.
    TEXT;

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $reason = $fields->get('reason');
        $startDate = Carbon::parse($fields->get('start_date'));
        $endDate = Carbon::parse($fields->get('end_date'));

        // Only act on the first model
        $activity = $models->first();
        \assert($activity instanceof Activity || $activity instanceof NovaActivity);

        // Ensure it' still writeable
        if ($activity->is_cancelled || $activity->end_date < now()) {
            return Action::danger('Deze activiteit kan niet meer worden aangepast.');
        }

        // Ensure user-input is sane
        if ($startDate >= $endDate || $startDate < now()) {
            return Action::danger('Kan activiteit niet uitstellen naar het verleden');
        }

        // Ensure values are right
        if ($startDate === $activity->start_date) {
            return Action::message('Geen verandering uitgevoerd');
        }

        if ($startDate < $activity->start_date) {
            return Action::danger('Kan activiteit niet uitstellen voor de oorspronkelijke start datum');
        }

        // Save old date and reason
        $activity->rescheduled_from = $activity->start_date;
        $activity->rescheduled_reason = $reason;

        // Remove postponed flag.
        $activity->postponed_at = null;
        $activity->postponed_reason = null;

        // Apply new date
        $activity->start_date = $startDate;
        $activity->end_date = $endDate;

        // Store changes
        $activity->save();

        // Inform enrolled users
        // TODO

        // Report
        return Action::message(sprintf(
            'De activiteit is verplaatst naar %s',
            $startDate->isoFormat('D MMM Y, HH:mm (z)'),
        ));
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
                ->help('De reden voor verplaatsing'),
            DateTime::make('Nieuwe aanvang', 'start_date')
                ->sortable()
                ->rules('required', 'date', 'after:now')
                ->firstDayOfWeek(1)
                ->help('Moet <em>na</em> de huidige startdatum zijn.'),
            DateTime::make('Nieuw einde', 'end_date')
                ->rules('required', 'date', 'after:start_date')
                ->hideFromIndex()
                ->firstDayOfWeek(1),
        ];
    }
}
