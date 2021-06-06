<?php

declare(strict_types=1);

namespace App\Mail\Shop;

use App\Models\Shop\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Shared elements for the shop mail
 *
 * @author Mark Walet <mark.walet@gmail.com>
 * @license MPL-2.0
 */
abstract class BaseShopMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The "reply to" recipients of the message.
     *
     * @var array
     */
    public $replyTo = [
        [
            'name' => 'Bestuur Gumbo Millennium',
            'address' => 'bestuur@gumbo-millennium.nl',
        ],
    ];

    /**
     * New order
     *
     * @var Order
     */
    public $order;

    /**
     * Create a new message instance.
     *
     * @param Order $order Submission to send
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->subject = $this->createSubject($order);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    abstract public function build();

    /**
     * Returns the subject
     *
     * @param Order $order
     * @return string
     */
    abstract protected function createSubject(Order $order): string;
}
