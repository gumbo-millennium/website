@extends('layout.variants.two-col')

@section('title', "Inschrijven voor {$activity->name} - Gumbo Millennium")

{{-- Set sidebar --}}
@section('two-col.right')

@component('activities.bits.sidebar', compact('activity'))
@slot('showTagline', false)
@slot('showMeta', true)
@endcomponent

@endsection

{{-- Set main --}}
@section('two-col.left')
<h1 class="text-3xl font-title mb-4">Inschrijven voor {{ $activity->title }}</h1>

<div class="leading-loose">
    <p class="mb-4">
        Wat leuk dat je mee wilt met {{ $activity->name }}. Er is een plek voor je gereserveerd, maar je moet
        wel voor {{ $enrollment->expire->isoDate('dddd D MMMM') }} je inschrijving afronden. Doe je dit niet, dan
        wordt je uitgeschreven en komt je plek weer vrij voor een ander.
    </p>
    <p>
        Allereerst wil de organisatie nog even wat gegevens van je weten.
    </p>
</div>

{{-- Medical warning --}}
@if ($isMedical)
@include('activities.enrollments.parts.form-medical')
@endif

{{-- Form --}}
@include('activities.enrollments.parts.form')
@endsection
