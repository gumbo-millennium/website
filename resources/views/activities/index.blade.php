@extends('layout.main')

@php
$title = 'Aankomende activiteiten';
$subtitle = 'Binnenkort op de agenda bij Gumbo Millennium';
if ($past) {
    $title = 'Afgelopen activiteiten';
    $subtitle = 'Overzicht van afgelopen activiteiten, tot 1 jaar terug.';
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
<div class="header header--activity">
    <div class="container header__container">
        <h1 class="header__title">{{ $title }}</h1>
        <p class="header__subtitle">{{ $subtitle }}</p>
    </div>
</div>

<div class="activity-blocks after-header">
    <div class="activity-block">
        <div class="container leading-loose">
            <p>
                Bij Gumbo houden wij van leuke activiteiten.
                Van een gezellige soosavond tot een spektaculair weekend weg in een prachtig landhuis.
            </p>
            <p>
                In onderstaand overzicht zie je de {{ Str::lower($title) }}.
            </p>
            @guest
            <div class="alert alert-info">
                Je bent niet ingelogd. Activiteiten die alleen toegankelijk zijn voor leden worden niet getoond.
            </div>
            @endguest
        </div>
    </div>

    @if (empty($activities))
    <div class="text-center mt-8 p-16">
        <h2 class="text-2xl font-normal text-center">Geen activiteiten</h2>
        <p class="text-center text-lg">De agenda is verdacht leeg. Kom later nog eens kijken.</p>
    </div>
    @else
    <div class="container pt-8">
        {{-- Activity cards --}}
        <div class="flex flex-row flex-wrap">
            @foreach ($activities as $activity)
            <div class="activity-grid__item mx-0 lg:w-1/3">
                @include('activities.bits.single')
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="container py-8">
    <p>
        @if ($past)
        <a href="{{ route('activity.index') }}">Toon alleen toekomstige evenementen</a>
        @else
        <a href="{{ route('activity.index', ['past' => true]) }}">Toon afgelopen evenementen</a>
        @endif
    </p>
</div>

@endsection
