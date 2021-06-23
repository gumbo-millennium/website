@extends('shop.layout')

{{-- Header --}}
@section('shop-title', "Bestelling {$order->number} annuleren")
@section('shop-subtitle', 'Toch maar niet?')

@section('shop-crumbs')
{{-- Breadcrumbs --}}
@breadcrumbs([
    'items' => [
        route('shop.home') => 'Shop',
        route('shop.order.index') => 'Bestellingen',
        route('shop.order.show', $order) => "Bestelling {$order->number}",
        'Annuleren'
    ]
])
@endbreadcrumbs
@endsection

{{-- Main --}}
@section('shop-content')
<form class="grid grid-col-1" id="cancel-form" method="post" action="{{ route('shop.order.cancel', $order) }}">
    @csrf

    <h3 class="text-xl font-title font-medium mb-4">Terugbetaling</h3>

    <div class="bg-gray-50 rounded-lg p-4 mb-2">
        @if ($isRefundable)
        @if ($isFullyRefundable)
            <strong class="text-lg font-title font-bold">Volledige terugbetaling</strong>
            <p class="mb-2 leading-loose">
                Je hebt recht op volledige teruggave van het aankoopbedrag.
            </p>
        @else
            <strong class="text-lg font-title font-bold">Gedeeltelijke terugbetaling</strong>
            <p class="mb-2 leading-loose">
                Je hebt recht op volledige teruggave van het aankoopbedrag, maar een deel is al teruggeboekt.<br />
                Je krijgt de resterende {{ Str::price($refundAmount) }} teruggeboekt zodra je annuleert.
            </p>
        @endif
        @if ($bankAccount = $refundInfo['accountNumber'] ?? null)
        <p class="mb-2 leading-loose">
            Het bedrag zal enkele dagen na annulering teruggestort worden op je bankrekening eindigend op
            {{ $bankAccount }}
        </p>
        @endif
        @else
        <strong class="text-lg font-title font-bold">Geen terugbetaling</strong>
        @if (!$isPaid)
        <p>
            Je wordt niet terugbetaald, omdat je nog niet hebt betaald.
        </p>
        @else
        <p>
            Het is niet mogelijk het bedrag terug te betalen. Indien je hier toch recht
            op hebt, moet je contact opnemen met het bestuur.
        </p>
        @endif
        @endif
    </div>

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
