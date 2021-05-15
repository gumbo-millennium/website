@extends('layout.main')

@section('content')
    {{-- Header --}}
    <div class="container mt-4 mb-8">
        <h1 class="font-title text-4xl font-bold mb-2">Bestelling bevestigen</h1>
    </div>

    {{-- Categories --}}
    <div class="container">
        <form method="post" target="{{ route('shop.order.store') }}">
            @csrf
            <div class="flex">
                <div class="w-1/2">
                    <p>Je bent er bijna! Nog even de laatste gegevens invullen en dan staat je bestelling vast. Je kan het!</p>
                    <button type="submit" class="btn btn--brand">
                        Bestellen
                    </button>
                </div>
                <div class="w-1/2">
                    @if ($cartItems->count() > 0)
                        @include('shop.partials.product-list', ['readonly' => true])
                    @else
                        <div class="p-8 text-center text-lg font-light border rounded border-gray-primary-1 text-gray-primary-1">
                            @lang('Your cart is empty')
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
