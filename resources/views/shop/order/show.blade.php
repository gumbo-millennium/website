@extends('shop.layout')

@php($user = Auth::user())
@php($breadcrumbs = [
  route('shop.home') => 'Shop',
  route('shop.order.index') => 'Bestellingen',
  "Bestelling {$order->number}"
])

{{-- Header --}}
@section('shop-title', "Bestelling {$order->number}")
@section('shop-subtitle')
@if ($order->cancelled_at)
Geannuleerd op {{ $order->cancelled_at->isoFormat('dddd D MMMM') }}.
@elseif ($order->paid_at)
Betaald op {{ $order->paid_at->isoFormat('dddd D MMMM') }}.
@else
Moet betaald worden voor {{ $order->expires_at->isoFormat('dddd D MMMM, HH:mm') }}.
@endif
@endsection

@section('shop-crumbs')
{{-- Breadcrumbs --}}
<x-breadcrumbs :items="$breadcrumbs" />
@endsection

{{-- Main --}}
@section('shop-content')
<div class="grid grid-col-1">
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
            @if ($order->shipped_at)
            Je hebt je bestelling afgehaald op {{ $order->shipped_at->isoFormat('dddd D MMMM') }}.
            @else
            Het bestuur neemt contact met je op voor het afhalen van je bestelling.
            @endif
        </p>
    </div>

    @if ($needsPayment)
    <h3 class="text-xl font-title font-medium mb-4">Betaling</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <strong class="text-lg font-title font-bold">iDEAL</strong>
        <p class="mb-2">
            @if ($order->paid_at)
            Je hebt je bestelling betaald op {{ $order->paid_at->isoFormat('dddd D MMMM') }}.
            @else
            Je moet deze bestelling betalen voor {{ $order->expires_at->isoFormat('dddd D MMMM') }}.
            @endif
        </p>
    </div>
    @endif

    @if ($needsPayment)
    <form method="POST" action="{{ route('shop.order.pay', [$order]) }}" id="pay-order">
        @csrf
    </form>

    <button method="submit" class="btn btn--brand text-center" form="pay-order" data-onclick="lock">
        Betaal bestelling
    </button>

    <a href="{{ route('shop.order.cancel', [$order]) }}" class="btn btn--link text-center">
        Annuleer bestelling
    </a>
    @endif
</div>
@endsection

@section('shop-sidebar')
    <h3 class="text-xl font-title font-medium mb-4">Jouw bestelling</h3>

    @include('shop.partials.order-list')
@endsection
