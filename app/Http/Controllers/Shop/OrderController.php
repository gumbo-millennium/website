<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Facades\Payments;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Models\Shop\Order;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Spatie\Flash\Flash;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends Controller
{
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
                ->toArray()
        );

        // Show confirmation page
        return ResponseFacade::view('shop.order.create', [
            'total' => Cart::getTotal(),
            'subTotal' => Cart::getSubTotal(),
            'cartItems' => Cart::getContent(),
        ])->header('Cache-Control', 'no-store');
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

        // Create order with Mollie
        $mollieOrder = Payments::createForOrder($order);
        $order->payment_id = $mollieOrder;
        $order->save();

        // Redirect to order
        return ResponseFacade::redirectToRoute('shop.order.pay', $order);
    }

    /**
     * Display the order, remains available after the order is paid.
     *
     * @throws NotFoundHttpException If the order is not found or if the user doens't match the order owner
     */
    public function show(Request $request, Order $order): Response
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        $order->hungry();

        // Whitelist images
        $this->addImageUrlsToCspPolicy(
            $order->variants->pluck('valid_image_url')->toArray()
        );

        // Render it
        return ResponseFacade::view('shop.order.show', [
            'order' => $order,
        ]);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        // Only allowing viewing your own orders
        abort_unless($request->user()->is($order->user), 404);

        $next = Payments::getRedirectUrl($order);

        if ($next) {
            return ResponseFacade::redirectTo($next);
        }

        if (! $next && Payments::isPaid($order)) {
            return ResponseFacade::redirectToRoute('shop.order.complete', $order);
        }

        flash()->info(
            __('Your previous payment is still pending, or this order cannot be paid right now. Try agan later.')
        );

        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }

    public function payReturn(Request $request, Order $order): RedirectResponse
    {
        \abort_if(! $order->user->is(Auth::user()), 404);

        if (Payments::isPaid($order)) {
            flash()->success(
                'Je betaling is ontvangen. Het kan even duren voordat dit zichtbaar is...'
            );

            return ResponseFacade::redirectToRoute('shop.order.show', $order);
        }

        flash()->info(
            'Je betaling is nog in behandeling of geannuleerd. Probeer het later nog eens.'
        );

        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }
}
