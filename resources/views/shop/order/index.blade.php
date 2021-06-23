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
    <div>
        <h3 class="font-title text-xl mb-4">Te betalen bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @forelse ($openOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @empty
            @include('shop.partials.order-empty', [
                'text' => 'Je hebt geen openstaande bestellingen',
            ])
        @endforelse
        </div>
    </div>

    <div>
        <h3 class="font-title text-xl mb-4">Te ontvangen bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @forelse ($paidOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @empty
            @include('shop.partials.order-empty', [
            'text' => 'Je hebt nog geen te-ontvangen bestellingen',
            ])
        @endforelse
        </div>
    </div>

    <div>
        <h3 class="font-title text-xl mb-4">Afgeronde bestellingen</h3>

        <div class="grid grid-cols-1 gap-4">
        @forelse ($completedOrders as $order)
            @include('shop.partials.order-tile', compact('order'))
        @empty
            @include('shop.partials.order-empty', [
                'text' => 'Je hebt nog geen bestellingen afgerond.',
            ])
        @endforelse
        </div>
    </div>

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
