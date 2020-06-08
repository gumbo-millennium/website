@extends('layout.variants.two-col')

@php
// User flags
$isMember = $user && $user->is_member;

// Get discount data
$hasRestrictedDiscount = $activity->discounts_available !== null;
$hasSoldOutDiscount = $activity->discounts_available === 0;

// Build a discount message
$discountWarning = (object) [
    'show' => $hasRestrictedDiscount && $isMember,
    'soldout' => $hasSoldOutDiscount,
    'price' => Str::price($activity->total_discount_price),
    'count' => sprintf(
        '%d %s',
        $activity->discounts_available,
        Str::multiple('lid', 'leden', $activity->discounts_available ?? 0)
    )
];

@endphp

{{-- Set title --}}
@section('title', "{$activity->name} - Activity - Gumbo Millennium")

{{-- Set sidebar --}}
@section('two-col.right')
@component('activities.bits.sidebar', compact('activity', 'is_enrolled', 'enrollment'))
    @slot('mainTitle', true)
    @slot('showJoin', true)
    @slot('showMeta', true)
@endcomponent
@endsection

{{-- Set main --}}
@section('two-col.left')
    {{-- Image --}}
    <div class="h-64 bg-gray-secondary-2 rounded mb-4 overflow-hidden" role="presentation">
        @if ($activity->image)
        <img
            class="w-full h-64 object-cover"
            src="{{ Storage::url(\App\Models\Activity::FILE_DISK, $activity->image) }}"
            srcset="
                {{ Storage::url(\App\Models\Activity::FILE_DISK, $activity->image) }} 384w,
                {{ Storage::url(\App\Models\Activity::FILE_DISK, $activity->image) }} 768w
            ">
        @else
        <div class="w-full h-64 flex items-center">
            <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="h-32 mx-auto block dark:hidden">
            <img src="{{ mix('images/logo-text-night.svg') }}" alt="Gumbo Millennium" class="h-32 mx-auto hidden dark:block">
        </div>
        @endif
    </div>

    {{-- Discount banner --}}
    @if ($discountWarning->show)
    <div class="notice notice--brand">
        @icon('solid/percentage', 'notice__icon')
        <p>
            @if ($discountWarning->soldout)
            De inschrijvingen met korting zijn allemaal vergeven, je betaald nu het normale tarief.
            @else
            Er geld een kortingstarief van {{ $discountWarning->price }}, deze is nog beschikbaar voor {{ $discountWarning->count }}.
            @endif
        </p>
    </div>
    @endif

    {{-- Activity description --}}
    @if (!empty($activity->description_html))
        <div class="leading-relaxed plain-content">
            {!! $activity->description_html !!}
        </div>
    @else
        <p class="leading-relaxed p-8 text-center text-gray-primary-1">
            Deze activiteit heeft geen uitgebreide omschrijving.
        </p>
    @endif
@endsection
