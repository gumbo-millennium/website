@extends('layout.main')

@php
// User flags
$isMember = $user && $user->is_member;

// Start date
$startLocale = $activity->start_date;
$startIso = $activity->start_date->toIso8601String();
$startDate = $startLocale->isoFormat('D MMM Y');
$startTime = $startLocale->isoFormat('HH:mm');
$startDateFull = $startLocale->isoFormat('D MMM Y, HH:mm (z)');

// Duration
$duration = $activity->start_date->diffAsCarbonInterval($activity->end_date);
$durationIso = $duration->spec();
$durationTime = $duration->forHumans(['parts' => 1]);
$headerClass = "header-activity-{$activity->slug}";

// Price and discount
$priceLabel = $activity->is_free ? 'Gratis toegang' : Str::ucfirst($activity->price_label);
$normalPrice = Str::price($activity->total_price);
$discountPrice = Str::price($activity->total_discount_price);

// Discount booleans
$hasDiscount = $activity->discount_price > 0;
$hasRestrictedDiscount = $activity->discounts_available !== null;
$hasSoldOutDiscount = $activity->discounts_available === 0;
$discountLabel = sprintf(
    '%d %s',
    $activity->discounts_available,
    Str::multiple('lid', 'leden', $activity->discounts_available ?? 0)
);

// Number of seats
$seats = 'Onbeperkt plaats';
if ($activity->available_seats === 0) {
    $seats = 'Uitverkocht';
} elseif ($activity->seats) {
    $seats = sprintf('%d van %d plekken beschikbaar', $activity->available_seats, $activity->seats);
}
@endphp

@if ($activity->image->exists())
@push('main.styles')
<style type="text/css" nonce="{{ csp_nonce() }}">
.{{ $headerClass }} {
    background-image: url('{{ $activity->image->url() }}');
}
</style>
@endpush
@endif

@section('title', "{$activity->name} - Activity - Gumbo Millennium")

@section('content')
<div class="header">
    <div class="activity-header-filler">&nbsp;</div>
    <div class="header__floating" role="presentation">
        {{ Str::ascii($activity->name) }}
    </div>
</div>
<div class="container">
    <div class="activity-summary">
        <div class="activity-summary__card">
            <div class="activity-summary__main">
                {{-- Title --}}
                <h1 class="text-2xl font-bold text-black">{{ $activity->name }}</h1>
                @if (!empty($activity->tagline))
                <h3 class="text-lg text-gray-700">{{ $activity->tagline }}</h3>
                @endif

                <div class="activity-summary__action-inline">
                @include('activities.bits.join-button')
                </div>

                {{-- Details --}}
                <div class="activity-summary__stats">
                    <div class="activity-summary__stat-group">
                        <div class="activity-summary__stat">
                            @icon('solid/clock', 'mr-4')
                            <time datetime="{{ $startIso }}">{{ $startDateFull }}</time>
                        </div>
                        <div class="activity-summary__stat">
                            @icon('solid/map-marker-alt', 'mr-4')
                            @empty($activity->location)
                            <span class="text-gray-600">Onbekend</span>
                            @elseif ($activity->location_url)
                            <a href="{{ $activity->location_url }}" target="_blank" rel="noopener">{{ $activity->location }}</a>
                            @else
                            {{ $activity->location }}
                            @endif
                        </div>
                    </div>
                    <div class="activity-summary__stat-group">
                        <div class="activity-summary__stat">
                            @icon('solid/user-friends', 'mr-4')
                            {{ $seats }}
                        </div>
                        <div class="activity-summary__stat">
                            @icon('solid/ticket-alt', 'mr-4')
                            {{ $priceLabel }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="activity-summary__action">
                @include('activities.bits.join-button')
            </div>
        </div>
    </div>

    <div class="my-8 px-12">
        {{-- Discount banner --}}
        @if ($hasDiscount)
        <div class="notice notice--brand">
            @icon('solid/percentage', 'notice__icon')
            <p>
                @if ($hasSoldOutDiscount && $isMember)
                Het gereduceerde tarief is helaas uitverkocht. Je betaald nu het reguliere tarief van {{ $normalPrice }}.
                @elseif ($isMember && $hasRestrictedDiscount)
                Het gereduceerde tarief van {{ $discountPrice }} is nog beschikbaar voor {{ $discountLabel }}. Hierna betaal je {{ $normalPrice }}.
                @elseif ($isMember)
                Als lid betaal je het gereduceerde tarief van {{ $discountPrice }}. Niet-leden betalen {{ $normalPrice }}.
                @else
                Het ledentarief bedraagt {{ $discountPrice }}, niet-leden betalen {{ $normalPrice }}.
                @endif
            </p>
        </div>
        @endif

        {{-- Activity description --}}
        <div class="leading-relaxed plain-content">
            {!! $activity->description_html !!}
        </div>
    </div>

    </div>
</div>
@endsection
