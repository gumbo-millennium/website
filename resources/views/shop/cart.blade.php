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
        @include('shop.partials.product-list', ['readonly' => false])
    @else
    <div class="p-8 text-center text-lg font-light border rounded border-gray-primary-1 text-gray-primary-1">
        @lang('Your cart is empty')
    </div>
    @endif
    <div class="flex justify-end">
        <a href="{{ route('shop.order.create') }}" class="btn btn--brand">Bestellen</a>
    </div>
</div>
@endsection
