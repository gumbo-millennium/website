<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Facades\Payments;
use App\Models\Enrollment;
use App\Models\Shop\Order;
use App\Models\User;
use App\Nova\Resources\Shop\Order as NovaOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;

class ShipOrder extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Ship';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Ship order';

    /**
     * The text to be used for the action's cancel button.
     *
     * @var string
     */
    public $cancelButtonText = 'Cancel';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Wil je deze bestelling markeren als afgegeven? Indien je \'m opstuurt kan je de bezorgdienst en tracking-code opgeven.';

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name(): string
    {
        return __($this->name);
    }

    /**
     * Makes a new Confirm Enrollment configured to this model.
     *
     * @return ConfirmEnrollment
     */
    public static function make(Order $order, User $user): self
    {
        // Prep proper text
        $noticeText = 'Wil je deze bestelling markeren als verzonden? Dit voorkomt annulering.';

        // Make instance
        return (new self())
            ->canSee(static fn () => ! Payments::isCompleted($order) && ! Payments::isCancelled($order) && Payments::isPaid(${$order}))
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
                Payments::isCancelled($order) ||
                ! Payments::isPaid($order)
            ) {
                return Action::danger("Kan {$order->number} niet als verzonden markeren.");
            }

            Payments::shipAll(
                $order,
                $fields->trackingCode ? $fields->carrier : null,
                $fields->trackingCode,
            );

            $order->shipped_at = Date::now();
            $order->save();
        }

        return Action::message('Bestelling gemarkeerd als verzonden.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Heading::make(__('Shipping information'))
                ->help(__('If you shipped this order, please fill in the fields below. If you handed the order off in person, skip \'em')),

            Select::make(__('Shipping Provider'), 'carrier')
                ->options([
                    'postnl' => 'PostNL',
                    'dpd' => 'DPD',
                    'dhl' => 'DHL',
                    'cycloon' => 'Cycloon',
                ])
                ->nullable(),

            Text::make(__('Track-and-Trace code'), 'trackingcode')
                ->nullable()
                ->help(__('Only required if you actually shipped this order, otherwise just click :send', [
                    'send' => __('Send')
                ])),
        ];
    }
}
