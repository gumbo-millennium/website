<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use App\Jobs\Payments\UpdateMollieSettlement;
use App\Models\Payments\Settlement;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class UpdateSettlement extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $validModels = $models->filter(fn ($row) => $row instanceof Settlement);
        if ($validModels->count() !== $models->count()) {
            return Action::danger(__("Some of the specified models aren't settlements."));
        }

        foreach ($validModels as $settlement) {
            UpdateMollieSettlement::dispatch($settlement->mollie_id);
        }

        return Action::message(__('Settlements will be updated shortly.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
