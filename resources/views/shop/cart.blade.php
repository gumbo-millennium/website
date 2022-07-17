@extends('shop.layout')

{{-- Header --}}
@section('shop-title', 'Jouw winkelwagen')
@section('shop-subtitle')
Je winkelwagen is <strong>geen</strong> reservering, dus lekker snel bestellen is aan te raden.
@endsection

@section('shop-crumbs')
{{-- Breadcrumbs --}}
<x-breadcrumbs :items="[
    route('shop.home') => 'Shop',
    'Winkelwagen',
]" />
@endsection

{{-- Main --}}
@section('shop-content')
@if ($cartItems->count() > 0)
    @include('shop.partials.product-list', ['readonly' => false])
@else
<div class="p-8 text-center text-lg font-white border rounded border-gray-500 text-gray-500">
    @lang('Your cart is empty')
</div>
@endif
<div class="flex justify-end">
    <a href="{{ route('shop.order.create') }}" class="btn btn--brand">Bestellen</a>
</div>
@endsection
