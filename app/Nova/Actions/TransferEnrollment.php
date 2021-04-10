<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Contracts\EnrollmentServiceContract;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\User as ModelsUser;
use App\Nova\Resources\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Sloveniangooner\SearchableSelect\SearchableSelect;

class TransferEnrollment extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Overschrijven';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return Action::danger('Kan maar één inschrijving per keer overschrijven');
        }

        $enrollment = Enrollment::find($models->first()->id);
        if (!$enrollment instanceof Enrollment || $enrollment->state instanceof Cancelled) {
            return Action::danger('Kan alleen actieve inschrijvingen overschrijven');
        }

        // Get current data
        $oldUser = $enrollment->user;
        $newUser = ModelsUser::find($fields->user_id);

        if ($newUser->is($oldUser)) {
            return Action::danger('Kan inschrijving niet naar zichzelf overschrijven');
        }

        // Get target data
        $activity = $enrollment->activity;
        $newUserEnrollment = Enrollment::findActive($newUser, $activity);

        if ($newUserEnrollment) {
            return Action::danger('Deze gebruiker is al ingeschreven, schrijf deze eerst uit.');
        }

        // Get transfer service
        $service = \app(EnrollmentServiceContract::class);
        \assert($service instanceof EnrollmentServiceContract);

        // Done
        $service->transferEnrollment($enrollment, $newUser);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Reden', 'reden'),
            SearchableSelect::make('Nieuwe deelnemer', 'user_id')
                ->rules('required')
                ->resource(User::class)
                ->max(5),
        ];
    }
}
