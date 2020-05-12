@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">Sponsoren van Gumbo Millennium</h1>
        <p class="page-hero__lead">Bedrijven die ons een warm hart toedragen, en ons de leukste activiteiten laten organiseren.</p>
    </div>
</div>

{{-- Main sponsors --}}
<div class="p-8 bg-gray-secondary-2">
    <div class="container">
        <div class="partner-block partner-block--primary">
            @foreach ($branded as $sponsor)
            @include('sponsors.partials.primary')
            @endforeach
        </div>
    </div>
</div>

{{-- Second sponsors and list --}}
<div class="container">
    {{-- Hero --}}
    <div class="page-hero page-hero--minor">
        {{-- <h2 class="page-hero__title">Nog meer awesome bedrijven</h2> --}}
        <p class="page-hero__lead">But wait, there's more!</p>
    </div>

    {{-- Smaller sponsors --}}
    <div class="partner-block partner-block--secondary">
        @foreach ($simple as $sponsor)
        @include('sponsors.partials.secondary')
        @endforeach
    </div>
</div>
@endsection
