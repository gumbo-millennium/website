<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Facades\Payments;
use App\Models\Enrollment;
use App\Models\Shop\Order;
use App\Models\User;
use App\Notifications\Shop\OrderCancelled;
use App\Notifications\Shop\OrderRefunded;
use App\Nova\Resources\Shop\Order as NovaOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use UnexpectedValueException;

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
    public $confirmButtonText = 'Bestelling annuleren';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Scherm sluiten';

    /**
     * Makes a new Confirm Enrollment configured to this model.
     *
     * @return ConfirmEnrollment
     */
    public static function make(Order $order, User $user): self
    {
        // Prep proper text
        $noticeText = 'Wil je deze bestelling annuleren?';
        if ($order->paid_at !== null) {
            $noticeText = ' De gebruiker wordt automatisch terugbetaald.';
        }

        // Make instance
        return (new self())
            ->canSee(function () use ($order) {
                try {
                    return ! Payments::isCompleted($order) && ! Payments::isCancelled($order);
                } catch (UnexpectedValueException $e) {
                    return false;
                }
            })
            ->confirmText($noticeText)
            ->onlyOnDetail();
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $order) {
            // Get the inner order
            if ($order instanceof NovaOrder) {
                $order = $order->model();
            }

            // Check type
            \assert($order instanceof Order);
            $order->loadMissing('user');

            if (
                Payments::isCompleted($order) ||
                Payments::isCancelled($order)
            ) {
                return Action::danger("Kan {$order->number} niet annuleren.");
            }

            $order->cancelled_at = Date::now();
            $order->save();

            Payments::cancelOrder($order);

            $notice = new OrderCancelled($order);

            if (Payments::isPaid($order)) {
                Payments::refundAll($order);

                $notice = new OrderRefunded($order, $order->price, '[N/A]');
            }

            optional($order->user)->notify($notice);
        }

        return Action::danger('Bestelling geannuleerd.');
    }
}
