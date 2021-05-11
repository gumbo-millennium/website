@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container mt-4 mb-8">
    <h1 class="font-title text-4xl font-bold mb-2">Jouw winkelwagen</h1>
    <h2 class="font-title text-2xl text-grey-primary-1">Je winkelwagen is <strong>geen</strong> reservering, dus lekker snel bestellen is aan te raden.</h2>
</div>

{{-- Categories --}}
<div class="container">
    @if ($cartItems->count() > 0)
    <table class="shop-cart">
        <thead>
            <tr>
                <th>Aantal</th>
                <th class="w-1/2">Product</th>
                <th>Eenheidsprijs</th>
                <th>Prijs</th>
                <th aria-label="Acties">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cartItems as $item)
            <tr>
                <td>
                    <form action="{{ route('shop.cart.update') }}" method="post" id="form-{{ $item->id }}">
                        @method('PATCH')
                        @csrf

                        <input type="hidden" name="id" value="{{ $item->id }}" />

                        <input class="shop-cart-amount appearance-none"  type="number" min="0" max="5" name="quantity" value="{{ $item->quantity }}">
                    </form>
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
                <td>
                    <button form="form-{{ $item->id }}" class="shop-cart-button mr-2" type="submit">Bijwerken</button>
                    <button form="form-{{ $item->id }}" class="shop-cart-button" type="submit" name="quantity" value="0">×</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="p-8 text-center text-lg font-light border rounded border-gray-primary-1 text-gray-primary-1">
        @lang('Your cart is empty')
    </div>
    @endif
</div>
@endsection
