@extends('shop.layout')

@php($user = Auth::user())

{{-- Header --}}
@section('shop-title', "Jouw bestellingen")

@section('shop-crumbs')
{{-- Breadcrumbs --}}
@breadcrumbs([
    'items' => [
        route('shop.home') => 'Shop',
        'Bestellingen',
    ]
])
@endbreadcrumbs
@endsection

{{-- Main --}}
@section('shop-content')
<p class="mb-8 text-lg">
    Hieronder staan jouw bestellingen, wil je een nieuwe bestelling plaatsen? <a href="{{ route('shop.home') }}">Bekijk dan de shop!</a>
</p>
<div class="grid gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    @if ($totalOrders === 0)
    <div class="md:col-span-2 lg:col-span-3">
        <div class="p-8 rounded border border-gray-400 text-gray-800 text-center">
            Je hebt nog helemaal niks besteld in de webshop!
        </div>
    </div>
    @endif
    @if($openOrders->isNotEmpty())
    <div>
        <h3 class="font-title text-xl mb-4">Te betalen bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @foreach ($openOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @endforeach
        </div>
    </div>
    @endif

    @if($paidOrders->isNotEmpty())
    <div>
        <h3 class="font-title text-xl mb-4">Te ontvangen bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @foreach ($paidOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @endforeach
        </div>
    </div>
    @endif

    @if($completedOrders->isNotEmpty())
    <div>
        <h3 class="font-title text-xl mb-4">Afgeronde bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @foreach ($completedOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @endforeach
        </div>
    </div>
    @endif

    @if($restOrders->isNotEmpty())
    <div>
        <h3 class="font-title text-xl mb-4">Overige bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @foreach ($restOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
