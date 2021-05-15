@php($readonly ??= true)

<table class="shop-cart">
    <thead>
        <tr>
            <th>Aantal</th>
            <th class="w-1/2">Product</th>
            <th>Eenheidsprijs</th>
            <th>Prijs</th>
            @unless($readonly)
                <th aria-label="Acties">&nbsp;</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($cartItems as $item)
            <tr>
                <td>
                    @if ($readonly)
                        {{ $item->quantity }}
                    @else
                        <form action="{{ route('shop.cart.update') }}" method="post" id="form-{{ $item->id }}">
                            @method('PATCH')
                            @csrf

                            <input type="hidden" name="id" value="{{ $item->id }}" />
                            <input class="shop-cart-amount appearance-none"  type="number" min="0" max="5" name="quantity" value="{{ $item->quantity }}">
                        </form>
                    @endif
                </td>
                <td>
                    {{ $item->name }}
                </td>
                <td>
                    {{ Str::price($item->price) }}
                </td>
                <td>
                    {{ Str::price($item->getPriceSum()) }}
                </td>
                @unless($readonly)
                    <td class="flex justify-end">
                        <button form="form-{{ $item->id }}" class="shop-cart-button mr-2" type="submit">Bijwerken</button>
                        <button form="form-{{ $item->id }}" class="shop-cart-button" type="submit" name="quantity" value="0">×</button>
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
