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
{{-- Header --}}
@include('content.home.header')

{{-- Corona message --}}
@include('content.home.corona')

{{-- Activities --}}
@include('content.home.activities')
@endsection
