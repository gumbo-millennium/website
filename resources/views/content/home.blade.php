@extends('layout.main')

@php
use Carbon\Carbon;
$leadTop = "Dubbel L, Dubbel N,";
$leadBig = "Dubbel genieten";

// Set the metadata
SEOMeta::setTitle('Welkom');
SEOMeta::setCanonical(url('/'));
@endphp

@push('header.navbar-class', ' navbar--no-shadow ')

@section('content')
<div class="container">
    <div class="home-hero">
        <div class="home-hero__text">
            <h2 class="home-hero__title">
                {{ $leadTop }}<br />
                <span class="home-hero__title-fat">{{ $leadBig }}</span>
            </h2>

            <p class="home-hero__lead">Welkom bij de gezelligste studentenvereniging van Zwolle.</p>

            <a href="{{ route('join.form') }}" class="btn btn--brand my-0">Word lid</a>
        </div>

        <div class="home-hero__logo">
            <img src="{{ mix('images/logo-glass-green.svg') }}" alt="Gumbo Millennium" class="home-hero__logo-image" />
        </div>
    </div>
</div>

{{-- Upcoming --}}
@if (!empty($nextEvents))
<div class="container pt-8">
    <p class="text-center text-gray-600 mb-4">Altijd iets te doen</p>
    <h2 class="text-3xl text-medium font-title mb-8 text-center">Binnenkort bij Gumbo Millennium</h2>
    {{-- Activity cards --}}
    <div class="card-grid">
        @foreach ($nextEvents as $activity)
        <div class="card-grid__item">
            @include('activities.bits.single')
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
