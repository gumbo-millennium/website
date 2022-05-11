<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Enums\PaymentStatus;
use App\Helpers\Str;
use App\Models\Shop\Order;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class ShopOrder extends Component
{
    private Order $order;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $order = $this->order;

        $orderLead = match ($order->status) {
            PaymentStatus::PAID => __('Paid'),
            PaymentStatus::CANCELLED => __('Cancelled'),
            PaymentStatus::EXPIRED => __('Expired'),
            PaymentStatus::OPEN => __('Open'),
        };

        $footerTitle = trans_choice('1 product|:count products', $order->variants->sum('count'));
        $footerText = __('Total value: :value', ['value' => Str::price($order->price)]);
        if ($order->status === PaymentStatus::OPEN) {
            $footerText = implode(' - ', [
                $footerText,
                $footerTitle,
            ]);
            $footerTitle = __('Payment required before :date', [
                'date' => $order->expires_at->isoFormat('ddd D MMMM'),
            ]);
        }

        // Done
        return View::make('components.card', [
            'href' => $order->url,
            'image' => $order->image_path,
            'title' => $order->number,

            'footerTitle' => $footerTitle,
            'footerText' => $footerText,
        ]);
    }
}
