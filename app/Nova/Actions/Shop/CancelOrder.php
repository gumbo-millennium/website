<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Models\Shop\Order;
use App\Nova\Resources\Shop\Order as NovaOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class CancelOrder extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Annuleren';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Cancel order';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Close window';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Are you sure you want to cancel this order? The user will be issued a refund if they\'ve completed payment.';

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
        $blockedItems = 0;

        foreach ($models as $order) {
            // Get the inner order
            if ($order instanceof NovaOrder) {
                $order = $order->model();
            }

            // Check type
            \assert($order instanceof Order);
            $order->loadMissing('user');

            if ($order->paid_at || $order->cancelled_at) {
                $blockedItems++;

                continue;
            }

            $order->cancelled_at = Date::now();
            $order->save();
        }

        $modelCount = $models->count();

        if ($blockedItems === 0 && $modelCount === 1) {
            return Action::message('Bestelling geannuleerd.');
        }

        if ($blockedItems === 0) {
            return Action::message('Alle bestellingen geannuleerd.');
        }

        if ($modelCount === 1) {
            return Action::danger('Bestelling kon niet worden geannuleerd.');
        }

        if ($modelCount === $blockedItems) {
            return Action::danger('Bestellingen konden niet worden geannuleerd.');
        }

        return Action::danger(sprintf(
            '%d van %d bestellingen geannuleerd.',
            ${$modelCount} - $failedOrders,
            $modelCount,
        ));
    }
}
