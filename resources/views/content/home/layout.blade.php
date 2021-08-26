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
{{-- Covid --}}
@includeWhen(!Auth::user() || Auth::user()->hasVerifiedEmail(), 'content.home.corona')

{{-- Header --}}
@include('content.home.header')

{{-- Sponsors --}}
@include('content.home.sponsors')

{{-- Grote Clubactie --}}
@includeWhen(now() < Carbon::parse('2020-12-10T00:00:00'), 'content.home.clubactie')

{{-- Shop --}}
@if ($advertisedProduct)
<div class="container my-8">
    @include('shop.partials.home-advert', ['product' => $advertisedProduct])
</div>
@endif

{{-- Activities --}}
@include('content.home.activities')

{{-- News --}}
@include('content.home.news')

{{-- Links (SEO) --}}
@include('content.home.links')
@endsection
