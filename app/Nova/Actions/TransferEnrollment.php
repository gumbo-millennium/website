<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Jobs\Stripe\CreateInvoiceJob;
use App\Jobs\Stripe\VoidInvoice;
use App\Models\Enrollment;
use App\Models\States\Enrollment\Cancelled;
use App\Models\States\Enrollment\Paid;
use App\Models\User as ModelsUser;
use App\Notifications\EnrollmentTransferred;
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
     * @var string
     */
    public $name = 'Overschrijven';

    /**
     * Perform the action on the given models.
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
            dd($enrollment, $enrollment->state);
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

        // Transfer enrollment
        $enrollment->user()->associate($newUser);

        // Check expire, making sure it's at least 2 days
        if (!$enrollment->state->isStable()) {
            $enrollment->expire = max($enrollment->expire, now()->addDays(2));
        }

        // Save changes
        $enrollment->save();

        // Send mails
        $oldUser->notify(new EnrollmentTransferred($enrollment, $oldUser));
        $newUser->notify(new EnrollmentTransferred($enrollment, $oldUser));

        // If not yet paid, make a new invoice
        if (!$enrollment->state instanceof Paid && $enrollment->price > 0) {
            VoidInvoice::withChain([
                new CreateInvoiceJob($enrollment)
            ])->dispatch($enrollment);
        }
    }

    /**
     * Get the fields available on the action.
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Reden', 'reden'),
            SearchableSelect::make('Nieuwe deelnemer', 'user_id')
                ->rules('required')
                ->resource(User::class)
                ->max(5)
        ];
    }
}
