<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Jobs\Payments\UpdatePaymentJob;
use App\Models\Shop\Order;
use App\Nova\Resources\Shop\Order as NovaOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UpdateOrder extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Bijwerken';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Update order';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'This will force an update of all payments, in case the order has gotten out-of-sync for some reason.';

    /**
     * Indicates if this action is available on the resource index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if this action is available on the resource's table row.
     *
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * Get the displayable name of the action.
     */
    public function name(): string
    {
        return __($this->name);
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $updatedItems = 0;

        foreach ($models as $order) {
            // Get the inner order
            if ($order instanceof NovaOrder) {
                $order = $order->model();
            }

            // Trigger an update for each payment
            foreach ($order->payments as $payment) {
                UpdatePaymentJob::dispatch($payment);
            }

            // Done
            $updatedItems++;
        }

        $totalItems = $models->count();

        if ($totalItems === $updatedItems) {
            return Action::message(__('All orders have been updated.'));
        }

        return Action::danger(__(':updated of :total order(s) have been updated.', [
            ':updated' => $updatedItems,
            ':total' => $totalItems,
        ]));
    }
}
