<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Activity;
use App\Nova\Actions\Traits\BlocksCancelledActivityRuns;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;

class ExportActivityParticipants extends Action
{
    use BlocksCancelledActivityRuns;
    use InteractsWithQueue;
    use Queueable;

    protected const TYPE_CHECK_IN = 'check-in';

    protected const TYPE_MEDICAL = 'medical';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Export opstellen';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = <<<'DOC'
    Exporteer de deelnemerslijst van deze activiteit.
    Indien de inschrijving medische gegevens bevat, wordt deze niet opgenomen in de export.
    DOC;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Exporteer gegevens';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Textarea::make('Reden', 'reason')
                ->rules('required', 'max:190')
                ->rows(4)
                ->help('De reden voor annulering, max 190 tekens'),
            Boolean::make('Betalingen terugboeken', 'refund_all')
                ->help('Alle uitgevoerde betalingen terugboeken'),
        ];
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $type = $fields->get('type', 'full');
        $activity = $models->first();

        if ($type === self::TYPE_ARCHIVE) {
            return $this->buildArchiveFile($activity);
        }

        if ($type === self::TYPE_CHECK_IN) {
            return $this->buildCheckIn($activity);
        }

        // Fail
        return Action::danger(__('Invalid export type requested'));
    }

    private function buildArchiveFile(Activity $activity)
    {
        return $this->download();
    }

    private function buildCheckIn(Activity $activity)
    {
        $titles = ['Achternaam', 'Voornaam', 'Tussenvoegsel', 'Status'];

        $enrollments = $activity->enrollments()->whereNotState();

        return $this->download();
    }

    private function downloadFile(string $path): void
    {
        // code...
    }
}
