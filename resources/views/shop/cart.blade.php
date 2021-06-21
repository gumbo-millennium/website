@extends('shop.layout')

{{-- Header --}}
@section('shop-title', 'Jouw winkelwagen')
@section('shop-subtitle')
Je winkelwagen is <strong>geen</strong> reservering, dus lekker snel bestellen is aan te raden.
@endsection

{{-- Main --}}
@section('shop-content')
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
@endsection
