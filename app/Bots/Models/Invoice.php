<?php

declare(strict_types=1);

namespace App\Bots\Models;

use App\Helpers\Str;
use App\Models\Shop\Order;
use App\Models\Shop\ProductVariant;
use Illuminate\Support\Facades\Config;

/**
 * @method self text(string $text)
 * @method self parseMode(string $parseMode)
 * @method self disableWebPagePreview(bool $disableWebPagePreview)
 * @method self disableNotification(bool $disableNotification)
 * @method self allowSendingWithoutReply(bool $allowSendingWithoutReply)
 * @method static self make(array|string $title = [], ?string $description = null)
 */
class Invoice extends TelegramObject
{
    public static function fromShopOrder(Order $order): self
    {
        $prices = $order->variants
            ->map(fn (ProductVariant $variant) => [
                'label' => "{$variant->pivot->quantity}x {$variant->display_name}",
                'amount' => $variant->pivot->price * $variant->pivot->quantity,
            ])
            ->values()
            ->toArray();

        $date = $order->created_at->isoFormat('dddd DD MMMM, HH:mm');
        $value = Str::price($order->price);

        return static::make([
            'title' => $order->number,
            'description' => "Jouw bestelling van ${date}, ter waarde van ${value}",
            'payload' => $order->number,
            'currency' => 'EUR',
            'prices' => $prices,
        ]);
    }

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this
            ->providerToken(Config::get('services.stripe.telegram-token'));
    }

    public function addProduct(string $name, int $quantity, int $price)
    {
        // code...
    }
}
