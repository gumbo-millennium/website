<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Mail\Shop\NewOrderBoardMail;
use App\Mail\Shop\NewOrderUserMail;
use App\Models\Shop\Order;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OrderController extends Controller
{
    public function show(Order $order): Response
    {
        \abort_if(! $order->user->is(Auth::user()), 404);

        $order->loadMissing(['variants', 'variants.product']);

        return ResponseFacade::view('shop.order.complete', [
            'order' => $order,
        ])->setPrivate();
    }

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
        ])->header('Cache-Control', 'no-store');
    }

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
            'price' => Cart::getTotal(),
        ]);
        $order->user()->associate($user);

        // Map cart to proper order table
        $variantWithAmount = $cartItems->mapWithKeys(static fn ($item) => [$item->associatedModel->id => [
            'quantity' => $item->quantity,
            'price' => $item->price,
        ]]);
        $order->variants()->sync($variantWithAmount);

        // Save changes
        $order->save();

        // Clean cart
        Cart::clear();

        // Send mail to user and board
        Mail::to($user)->queue(new NewOrderUserMail($order));
        Mail::to(Config::get('gumbo.mail-recipients.board'))->queue(new NewOrderBoardMail($order));

        // Redirect to order
        return ResponseFacade::redirectToRoute('shop.order.show', $order);
    }
}
