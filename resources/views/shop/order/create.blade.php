@extends('layout.main')

@section('content')
    {{-- Header --}}
    <div class="container mt-4 mb-8">
        <h1 class="font-title text-4xl font-bold mb-2">Bestelling bevestigen</h1>
    </div>

    {{-- Categories --}}
    <div class="container">
        <div class="flex">
            <div class="w-2/3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid aspernatur blanditiis, dicta dolor eos esse inventore iste magnam maxime nostrum porro, quae quisquam ratione repellendus repudiandae sapiente sed, tenetur voluptates.</div>
            <div class="w-1/3">
                @if ($cartItems->count() > 0)
                    @include('shop.partials.product-list', ['readonly' => true])
                @else
                    <div class="p-8 text-center text-lg font-light border rounded border-gray-primary-1 text-gray-primary-1">
                        @lang('Your cart is empty')
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
