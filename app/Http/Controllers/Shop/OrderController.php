<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CartAddRequest;
use App\Http\Requests\Shop\CartUpdateRequest;
use App\Models\Shop\Order;
use App\Models\Shop\ProductVariant;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFacade;

class OrderController extends Controller
{
    public function create(): Response
    {
        return ResponseFacade::view('shop.order.create', [
            'total' => Cart::getTotal(),
            'subTotal' => Cart::getSubTotal(),
            'cartItems' => Cart::getContent(),
        ])->header('Cache-Control', 'no-store');
    }

    public function store(): Response
    {

    }

    public function show(Order $order): Response
    {
        abort_if(! $order->user->is(Auth::user()), 404);

        $order->loadMissing(['variants', 'variants.product']);

        return ResponseFacade::view('shop.order.complete', [
            'order' => $order,
        ])->setPrivate();
    }
}
