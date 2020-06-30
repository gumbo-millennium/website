@extends('layout.variants.two-col')

@section('title', "Inschrijving overnemen - {$activity->name} - Gumbo Millennium")

{{-- Set sidebar --}}
@section('two-col.right')
@component('activities.bits.sidebar', compact('activity'))
@slot('showTagline', true)
@slot('showMeta', true)
@slot('nextLink', 'activity')
@endcomponent
@endsection

{{-- Set main --}}
@section('two-col.left')
<h1 class="text-3xl font-title mb-4">Inschrijving overnemen</h1>

<div class="leading-loose">
    <p class="mb-4">
        {{ $enrollment->user->name }} heeft de inschrijving voor {{ $activity->name }} aangeboden voor overdracht.
        Hiermee wordt
        het ticket voor de activiteit direct overgedragen naar jou, zonder take-backsies.
    </p>
    <p class="mb-8 text-lg">
        Wil jij de inschrijving van {{ $enrollment->user->first_name }} overnemen?
    </p>

    <form action="{{ $nextUrl }}" method="post">
        @csrf
        <p>
            <button type="submit" class="btn btn--brand">Inschrijving overnemen</button>
        </p>
    </form>
</div>
@endsection
