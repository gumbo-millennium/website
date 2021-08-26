<div>
    <h3 class="text-xl font-title font-medium mb-4">Jouw winkelwagen</h3>
    @if ($cartItems->count() > 0)
    @include('shop.partials.product-list')
    @else
    <div class="p-8 text-center text-lg font-light border rounded border-gray-primary-1 text-gray-primary-1">
        @lang('Your cart is empty')
    </div>
    @endif
</div>
