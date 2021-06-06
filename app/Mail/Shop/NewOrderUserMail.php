<?php

declare(strict_types=1);

namespace App\Mail\Shop;

use App\Models\Shop\Order;

/**
 * Email sent to the board concerning new order submissions.
 *
 * @author Mark Walet <mark.walet@gmail.com>
 * @license MPL-2.0
 */
class NewOrderUserMail extends BaseShopMail
{
    /**
     * Board should not reply to these mails.
     *
     * @var array
     */
    public $replyTo = [];

    /**
     * @inheritDoc
     */
    public function build()
    {
        return $this->markdown('mail.shop.user');
    }
    /**
     * @inheritDoc
     */
    protected function createSubject(Order $order): string
    {
        return sprintf('[site] Nieuwe bestelling van %s.', $order->user->name);
    }
}
