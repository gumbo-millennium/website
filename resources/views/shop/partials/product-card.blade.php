<div>
    <h3 class="text-xl font-title font-medium mb-4">Jouw winkelwagen</h3>
    @if ($cartItems->count() > 0)
    @include('shop.partials.product-list')
    @else
    <div class="p-8 text-center text-lg font-white border rounded border-gray-500 text-gray-500">
        @lang('Your cart is empty')
    </div>
    @endif
</div>
