@extends('shop.layout')

@php($breadcrumbs = [
  route('shop.home') => 'Shop',
  route('shop.order.index') => 'Bestellingen',
  route('shop.order.show', $order) => "Bestelling {$order->number}",
  'Annuleren',
])

{{-- Header --}}
@section('shop-title', "Bestelling {$order->number} annuleren")
@section('shop-subtitle', 'Toch maar niet?')

@section('shop-crumbs')
{{-- Breadcrumbs --}}
<x-breadcrumbs :items="$breadcrumbs" />
@endsection

{{-- Main --}}
@section('shop-content')
<form class="grid grid-col-1" id="cancel-form" method="post" action="{{ route('shop.order.cancel', $order) }}">
    @csrf

    <h3 class="text-xl font-title font-medium mb-4">Are you sure?</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-2">
        <p class="text-lg">
            Weet je zeker dat je deze bestelling wil annuleren?
        </p>
    </div>

    <div class="hidden md:grid grid-cols-1 md:grid-cols-3 gap-2">
        <a href="{{ route('shop.order.show', [$order]) }}" class="btn btn--link text-center lg:col-span-2">
            Niet annuleren
        </a>

        <button type="submit" class="btn btn--brand text-center">
            Annuleer bestelling
        </button>
    </div>
</form>
@endsection

@section('shop-sidebar')
    <h3 class="text-xl font-title font-medium mb-4">Jouw bestelling</h3>

    @include('shop.partials.order-list')


    <div class="grid grid-cols-1 gap-2 md:hidden">
        <a href="{{ route('shop.order.show', [$order]) }}" class="btn btn--link text-center lg:col-span-2">
            Niet annuleren
        </a>

        <button form="cancel-form" type="submit" class="btn btn--brand text-center">
            Annuleer bestelling
        </button>
    </div>
@endsection
