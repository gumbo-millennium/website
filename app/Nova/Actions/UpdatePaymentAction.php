<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Helpers\Makeable;
use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UpdatePaymentAction extends Action
{
    use InteractsWithQueue;
    use Makeable;
    use Queueable;

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var Payment $payment */
        foreach ($models as $model) {
            if (! $model instanceof Payment) {
                continue;
            }

            UpdatePaymentJob::dispatch($model);
        }

        return $this->message(__('The update has been requested.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
