<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Enums\PaymentStatus;
use App\Facades\Payments;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Models\Payment;
use App\Models\Shop\Order;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        return ResponseFacade::view('shop.order.index', [
            'totalOrders' => $orders->count(),
            'openOrders' => (clone $orders)->where('status', PaymentStatus::OPEN),
            'paidOrders' => (clone $orders)->where('status', PaymentStatus::PAID),
            'restOrders' => (clone $orders)->whereNotIn('status', [PaymentStatus::OPEN, PaymentStatus::PAID]),
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
        // Check for tokens and redirect if set
        $idempotencyToken = $request->has('idempotency_token')
            ? "itempotency.order.{$request->post('idempotency_token')}"
            : null;
        if ($idempotencyToken && $next = Session::get($idempotencyToken)) {
            return RedirectResponse::to($next);
        }

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

        // Assign idempotency token
        if ($idempotencyToken) {
            Session::put($idempotencyToken, route('shop.order.show', [$order]));
        }

        // Assign variants, mapped as a proper table
        $variantWithAmount = $cartItems->mapWithKeys(static fn ($item) => [$item->associatedModel->id => [
            'quantity' => $item->quantity,
            'price' => $item->price,
        ]]);
        $order->variants()->sync($variantWithAmount);

        // Clean cart
        Cart::clear();

        // Redirect to order
        return ResponseFacade::redirectToRoute('shop.order.show', $order);
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

        // Hungry, Hungry, Model
        $order->hungry();

        // Render it
        return ResponseFacade::view('shop.order.show', [
            'order' => $order,
            'needsPayment' => $order->status === PaymentStatus::OPEN,
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function pay(Request $request, Order $order)
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        // Check if the order needs payment
        $payment = $order->payments->first() ?? null;
        if ($payment && $payment->is_stable) {
            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Create the order
        if (! $payment) {
            $payment = Payments::create($order);
        }

        // Redirect to 'please wait' page
        return ResponseFacade::redirectToRoute('payment.show', [$payment]);
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

        if ($order->cancelled_at) {
            flash()->info(
                'Deze bestelling is al geannuleerd. Is 1x annuleren niet genoeg?',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        if ($order->paid_at) {
            flash()->info(
                'Deze bestelling is afgerond. Als je niet tevreden bent met je aankoop, mag je contact opnemen met het bestuur.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Render it
        return ResponseFacade::view('shop.order.cancel', [
            'order' => $order,
            'isPaid' => $order->paid_at !== null,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        // Check if cancellable
        if ($order->cancelled_at) {
            flash()->info(
                'Deze bestelling is al geannuleerd. Is 1x annuleren niet genoeg?',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        if ($order->paid_at) {
            flash()->info(
                'Deze bestelling is afgerond. Als je niet tevreden bent met je aankoop, mag je contact opnemen met het bestuur.',
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        // Save as cancelled before API calls
        $order->cancelled_at = Date::now();
        $order->save();

        // Cancel at Mollie
        foreach ($order->payments as $payment) {
            Payments::find($payment->provider)->cancel($payment);
        }

        // Done
        flash()->info(
            'De bestelling is geannuleerd.',
        );

        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }
}
