<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Contracts\Payments\PayableModel;
use App\Facades\Payments;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Models\Shop\Order;
use App\Notifications\Shop\OrderRefunded;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

class OrderController extends Controller
{
    /**
     * List of placed orders.
     */
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->whereHas('user', fn (Builder $query) => $query->whereId($request->user()->id))
            ->with([
                'variants',
                'variants.product',
            ])
            ->where(function (Builder $query) {
                return $query->where('cancelled_at', '>', Date::now()->subMonth())
                    ->orWhereNull('cancelled_at');
            })
            ->orderBy('created_at')
            ->get();

        // Whitelist images
        $this->addImageUrlsToCspPolicy(
            $orders->map(
                fn (Order $order) => $order
                    ->variants
                    ->first()
                    ->valid_image_url,
            )->toArray(),
        );

        return ResponseFacade::view('shop.order.index', [
            'totalOrders' => $orders->count(),
            'openOrders' => $orders->where('payment_status', PayableModel::STATUS_OPEN),
            'paidOrders' => $orders->where('payment_status', PayableModel::STATUS_PAID),
            'completedOrders' => $orders->where('payment_status', PayableModel::STATUS_COMPLETED),
            'restOrders' => $orders->whereNotIn('payment_status', [
                PayableModel::STATUS_COMPLETED,
                PayableModel::STATUS_PAID,
                PayableModel::STATUS_OPEN,
            ]),
        ])->header('Cache-Control', 'no-cache, no-store');
    }

    /**
     * Shows the current shopping cart. It does NOT yet check if the
     * inventory allows for this order to fly, that's up to the
     * board to fix.
     */
    public function create(): SymfonyResponse
    {
        // Disallow competing cart if cart is empty
        if (Cart::getTotalQuantity() === 0) {
            return ResponseFacade::redirectToRoute('shop.cart');
        }

        // Whitelist images
        $this->addImageUrlsToCspPolicy(
            Cart::getContent()
                ->map(fn ($item) => $item->associatedModel->valid_image_url)
                ->toArray(),
        );

        // Show confirmation page
        return ResponseFacade::view('shop.order.create', [
            'total' => Cart::getTotal(),
            'subTotal' => Cart::getSubTotal(),
            'cartItems' => Cart::getContent(),
        ])->header('Cache-Control', 'no-cache, no-store');
    }

    /**
     * Creates an order from the cart in the session.
     * Orders expire 15 minutes after creation, so some haste to pay is advisable.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        // Disallow competing cart if cart is empty
        if (Cart::getTotalQuantity() === 0) {
            return ResponseFacade::redirectToRoute('shop.cart');
        }

        // Get data
        $user = $request->user();
        $cartItems = Cart::getContent();

        // Create order
        $order = new Order([
            'fee' => Cart::getTotal() - Cart::getSubTotal(),
            'price' => Cart::getTotal(),
        ]);
        $order->user()->associate($user);

        // Save changes
        $order->save();

        // Assign variants, mapped as a proper table
        $variantWithAmount = $cartItems->mapWithKeys(static fn ($item) => [$item->associatedModel->id => [
            'quantity' => $item->quantity,
            'price' => $item->price,
        ]]);
        $order->variants()->sync($variantWithAmount);

        // Clean cart
        Cart::clear();

        // Redirect to order
        return ResponseFacade::redirectToRoute('shop.order.pay', $order);
    }

    /**
     * Display the order, remains available after the order is paid.
     *
     * @throws NotFoundHttpException If the order is not found or if the user doens't match the order owner
     * @return RedirectResponse|Response
     */
    public function show(Request $request, Order $order)
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        try {
            $isCancelled = Payments::isCancelled($order);
            $isCompleted = Payments::isCompleted($order);
            $isPaid = Payments::isPaid($order);
        } catch (UnexpectedValueException $error) {
            flash()->info(
                __('Deze bestelling zit momenteel in limbo, we laten het je weten als hij beschikbaar is.'),
            );

            return ResponseFacade::redirectToRoute('shop.order.index');
        }

        // Hungry, Hungry, Model
        $order->hungry();

        // Whitelist images
        $this->addImageUrlsToCspPolicy(
            $order->variants->pluck('valid_image_url')->toArray(),
        );

        // Render it
        return ResponseFacade::view('shop.order.show', [
            'order' => $order,
            'needsPayment' => ! ($isCompleted || $isCancelled || $isPaid),
            'isCancellable' => ! ($isCompleted || $isCancelled),
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function pay(Request $request, Order $order)
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        // Check if cancelled
        if (Payments::isCancelled($order)) {
            flash()->info(
                'Deze bestelling is geannuleerd, dus je kan \'m niet meer betalen.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Or paid
        if (Payments::isPaid($order)) {
            flash()->info(
                'Deze bestelling is al betaald.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Redirect to 'please wait' page
        return ResponseFacade::view('shop.order.pay')
            ->header('Refresh', '0;url=' . route('shop.order.pay-redirect', $order))
            ->header('Cache-Control', 'no-store, no-cache');
    }

    /**
     * Redirect to Mollie to pay, or back if that's not neccesary.
     */
    public function payRedirect(Request $request, Order $order): RedirectResponse
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        // Can't pay cancelled or paid orders
        if (Payments::isCancelled($order) || Payments::isPaid($order)) {
            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        sleep(2);

        // Fetch order to check if set
        $mollieOrder = Payments::findOrder($order);

        // Create order with Mollie if it doesn't exist yet.
        if (! $mollieOrder) {
            $mollieOrder = Payments::createOrder($order);
            $order->payment_id = $mollieOrder->id;
            $order->save();
        }

        if ($next = Payments::getRedirectUrl($order)) {
            return ResponseFacade::redirectTo($next);
        }

        if (! $next && Payments::isPaid($order)) {
            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        flash()->info(
            __('Your previous payment is still pending, or this order cannot be paid right now. Try agan later.'),
        );

        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }

    /**
     * Quick redirect after the user returns from Mollie.
     */
    public function payReturn(Request $request, Order $order): RedirectResponse
    {
        \abort_if(! $order->user->is(Auth::user()), 404);

        if (Payments::isPaid($order)) {
            flash()->success(
                'Je betaling is ontvangen. Het kan even duren voordat dit zichtbaar is...',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        flash()->info(
            'Je betaling is nog in behandeling of geannuleerd. Probeer het later nog eens.',
        );

        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }

    /**
     * Cancellation form.
     *
     * @return RedirectResponse|Response
     */
    public function cancelShow(Request $request, Order $order)
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        if (Payments::isCancelled($order)) {
            flash()->info(
                'Deze bestelling is al geannuleerd. Is 1x annuleren niet genoeg?',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        if (Payments::isCompleted($order)) {
            flash()->info(
                'Deze bestelling is afgerond. Als je niet tevreden bent met je aankoop, mag je contact opnemen met het bestuur.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        $order->hungry();

        // Whitelist images
        $this->addImageUrlsToCspPolicy(
            $order->variants->pluck('valid_image_url')->toArray(),
        );

        // Find refund info
        $refundInfo = $this->getRefundInfo($order);

        // Render it
        return ResponseFacade::view('shop.order.cancel', [
            'order' => $order,
            'isPaid' => Payments::isPaid($order),
            'refundAmount' => $refundInfo['amount'],
            'isRefundable' => $refundInfo['isRefundable'],
            'isFullyRefundable' => $refundInfo['isFullyRefundable'],
            'refundInfo' => $refundInfo,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        // Check if cancellable
        if (Payments::isCancelled($order)) {
            flash()->info(
                'Deze bestelling is al geannuleerd. Is 1x annuleren niet genoeg?',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        if (Payments::isCompleted($order)) {
            flash()->info(
                'Deze bestelling is afgerond. Als je niet tevreden bent met je aankoop, mag je contact opnemen met het bestuur.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Save as cancelled before API calls
        $order->cancelled_at = Date::now();
        $order->save();

        // Cancel at Mollie
        Payments::cancelOrder($order);

        // Skip if not paid
        if (! Payments::isPaid($order)) {
            flash()->info(
                'De bestelling is geannuleerd.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Refund
        Payments::refundAll($order);

        // Find refund info
        $refundInfo = $this->getRefundInfo($order);

        // Only notify if number is non-zero
        if (! empty($refundInfo['accountNumber'])) {
            $order->user->notify(new OrderRefunded(
                $order,
                $refundInfo['amount'],
                $refundInfo['accountNumber'],
            ));
        }

        flash()->info(
            'De bestelling is geannuleerd, je krijgt je geld binnen een paar dagen terug.',
        );

        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }

    private function getRefundInfo(Order $order): array
    {
        $completedPayment = optional(Payments::getCompletedPayment($order));

        // Base info
        $refundInfo = [
            'amount' => $amount = (int) ($completedPayment->getAmountRemaining() * 100),
            'isRefundable' => $completedPayment->canBeRefunded(),
            'isFullyRefundable' => $amount === $order->price,
            'type' => null,
            'accountNumber' => null,
        ];

        // iDEAL refund info
        if ($accountNumber = object_get($completedPayment, 'details.consumerAccount')) {
            return array_merge($refundInfo, [
                'type' => 'ideal',
                'accountNumber' => substr($accountNumber, -4),
            ]);
        }

        // Wire transfer refund info
        if ($accountNumber = object_get($completedPayment, 'details.bankAccount')) {
            return array_merge($refundInfo, [
                'type' => 'banktransfer',
                'accountNumber' => substr($accountNumber, -4),
            ]);
        }

        // Unknown (default)
        return $refundInfo;
    }
}
