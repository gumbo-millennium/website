@extends('shop.layout')

@php($user = Auth::user())
@php($expiry = Carbon\Carbon::today()->addDay(2));

{{-- Header --}}
@section('shop-title', 'Bestelling bevestigen')

@section('shop-crumbs')
{{-- Breadcrumbs --}}
@breadcrumbs([
    'items' => [
        route('shop.home') => 'Shop',
        route('shop.cart') => 'Winkelwagen'
    ]
])
@endbreadcrumbs
@endsection

{{-- Main --}}
@section('shop-content')
<form class="grid grid-col-1" method="post" action="{{ route('shop.order.store') }}">
    @csrf

    <h3 class="text-xl font-title font-medium mb-4">Factuuradres</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-2">
        <strong class="text-lg font-title font-bold">{{ $user->name }}</strong>
        <p>
            {{ $user->address_string }}
        </p>
    </div>

    <p class="text-sm text-gray-600 mb-4 px-4">Jouw factuuradres wordt alleen gebruikt voor het bonnetje, en komt uit de ledenadministratie.</p>

    <h3 class="text-xl font-title font-medium mb-4">Bezorging</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <strong class="text-lg font-title font-bold">Afhalen bij het bestuur</strong>
        <p>
            Het bestuur neemt contact met je op voor het afhalen van je bestelling.
        </p>
    </div>

    <h3 class="text-xl font-title font-medium mb-4">Betaling</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <strong class="text-lg font-title font-bold">iDEAL</strong>
        <p class="mb-2">
            De kosten voor betaling via iDEAL bedragen {{ Str::price(Cart::getTotal() - Cart::getSubTotal()) }}.<br />
            Deze zijn opgenomen in je totaalbedrag rechts.
        </p>
    </div>

    <h3 class="text-xl font-title font-medium mb-4">Deadline</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <p>
            Je hebt na plaatsing van je bestelling tot
            <time datetime="{{ $expiry->format('Y-m-d') }}" class="font-bold">{{ $expiry->isoFormat('dddd DD MMMM') }}</time>
            om te betalen, anders wordt je bestelling geannuleerd.
        </p>
    </div>

    <button type="submit" class="btn btn--brand">
        Plaats bestelling
    </button>
</form>
@endsection

@section('shop-sidebar')
    @include('shop.partials.product-card', ['readonly' => true])
@endsection
