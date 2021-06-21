<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Facades\Payments;
use App\Http\Controllers\Controller;
use App\Mail\Shop\NewOrderBoardMail;
use App\Mail\Shop\NewOrderUserMail;
use App\Models\Shop\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response as ResponseFacade;
use InvalidArgumentException;

class ShopController extends Controller
{
    /**
     * Handle Mollie webhooks.
     *
     * @throws InvalidArgumentException
     */
    public function webhook(Request $request): Response
    {
        $id = $request->post('id');
        if (! $id) {
            return ResponseFacade::noContent(Response::HTTP_BAD_REQUEST);
        }

        $order = Order::query()->where('payment_id', $id)->first();
        if (! $order) {
            return ResponseFacade::noContent(Response::HTTP_OK);
        }

        if ($order->paid_at !== null) {
            return ResponseFacade::noContent(Response::HTTP_OK);
        }

        $paymentDate = Payments::paidAt($order);
        if ($paymentDate === null) {
            return ResponseFacade::noContent(Response::HTTP_OK);
        }

        // Update object
        $order->paid_at = $paymentDate;
        $order->save();

        return ResponseFacade::noContent(Response::HTTP_OK);
    }
}
