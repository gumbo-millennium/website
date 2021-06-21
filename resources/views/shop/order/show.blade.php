@extends('shop.layout')

@php($user = Auth::user())

{{-- Header --}}
@section('shop-title', "Bestelling {$order->number}")
@section('shop-subtitle')
@if ($order->status === 'pending')
Moet betaald worden voor {{ $order->expires_at->isoFormat('dddd DD MMMM') }}.
@elseif ($order->status === 'shipped')
Afgeleverd op {{ $order->shipped_at->isoFormat('dddd DD MMMM') }}.
@elseif ($order->status === 'paid')
Betaald op {{ $order->paid_at->isoFormat('dddd DD MMMM, H:mm') }}.
@endif
@endsection

@section('shop-crumbs')
{{-- Breadcrumbs --}}
@breadcrumbs([
    'items' => [
        route('shop.home') => 'Shop',
        '' => 'Bestellingen',
        '' => "Bestelling {$order->number}"
    ]
])
@endbreadcrumbs
@endsection

{{-- Main --}}
@section('shop-content')
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
            Je hebt je bestelling afgehaald op {{ $order->shipped_at->isoFormat('dddd DD MMMM') }}.
            @else
            Het bestuur neemt contact met je op voor het afhalen van je bestelling.
            @endif
        </p>
    </div>

    <h3 class="text-xl font-title font-medium mb-4">Betaling</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <strong class="text-lg font-title font-bold">iDEAL</strong>
        <p class="mb-2">
            @if ($order->paid_at)
            Je hebt je bestelling betaald op {{ $order->paid_at->isoFormat('dddd DD MMMM') }}.
            @else
            Je moet deze bestelling betalen voor {{ $order->expires_at->isoFormat('dddd DD MMMM') }}.
            @endif
        </p>
    </div>

    @if ($order->paid_at === null)
    <a href="{{ route('shop.order.pay', [$order]) }}" class="btn btn--brand">
        Betaal bestelling
    </a>
    @endif

</form>
@endsection

@section('shop-sidebar')
    <h3 class="text-xl font-title font-medium mb-4">Jouw bestelling</h3>

    @include('shop.partials.order-list')
@endsection
