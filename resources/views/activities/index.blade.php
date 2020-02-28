@extends('layout.main')

@php
$title = 'Aankomende activiteiten';
$subtitle = 'Binnenkort op de agenda bij Gumbo Millennium';
if ($past) {
    $title = 'Afgelopen activiteiten';
    $subtitle = 'Overzicht van afgelopen activiteiten.';
}

// Get first activity
$firstActivity = $past ? null : $activities->first();
@endphp

@section('title', "{$title} - Gumbo Millennium")

@if ($firstActivity && $firstActivity->image->exists())
@push('css')
<style nonce="@nonce">
.header--activity {
    background-image: url("{{ $firstActivity->image->url('banner') }}");
}
</style>
@endpush
@endif

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">{{ $title }}</h1>
        <p class="page-hero__lead">{{ $subtitle }}</p>
    </div>
</div>

<div class="activity-blocks">
    @if (empty($activities))
    <div class="text-center p-16">
        <h2 class="text-2xl font-normal text-center">Geen activiteiten</h2>
        <p class="text-center text-lg">De agenda is verdacht leeg. Kom later nog eens kijken.</p>
    </div>
    @else
    <div class="container">
        {{-- Activity cards --}}
        <div class="flex flex-row flex-wrap">
            @foreach ($activities as $activity)
            <div class="activity-grid__item mx-0 lg:w-1/3">
                @include('activities.bits.single')
            </div>
            @endforeach
        </div>
    </div>

    <div class="container pt-8">
        {{ $activities->links() }}
    </div>
    @endif
</div>

@endsection
