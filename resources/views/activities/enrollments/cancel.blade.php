@extends('layout.variants.two-col')

@section('title', "Uitschrijven - {$activity->name} - Gumbo Millennium")

{{-- Set sidebar --}}
@section('two-col.right')
@component('activities.bits.sidebar', compact('activity'))
    @slot('showTagline', false)
    @slot('showMeta', true)
@endcomponent
@endsection

{{-- Set main --}}
@section('two-col.left')
<h1 class="text-3xl font-title mb-4">Inschrijving annuleren</h1>

<div class="leading-loose mb-4">
    <p>
        Indien je wil, kan je je via onderstaande knop uitschrijven voor {{ $activity->title }}.
    </p>
    @if ($activity->available_seats > 0 && $activity->available_seats < 5)
        <p>
            Deze activiteit is <em>bijna</em> uitverkocht. Door je uit te schrijven voor deze activiteit
            komt je plek onmiddelijk vrij. Het kan zijn dat je hierdoor zelf niet meer opnieuw kan inschrijven.
        </p>
    @endif
</div>

<div class="leading-loose">
    <p>Klik hieronder om je uit te schrijven.</p>

</div>

<form action="{{ route('enroll.remove', compact('activity')) }}" method="post">
    @method('DELETE')
    @csrf
    <input type="hidden" name="accept" value="1">

    <div class="flex flex-col items-stretch lg:flex-row lg:items-center lg:justify-end">
        <a href="{{ route('activity.show', compact('activity')) }}" class="btn btn--link md:mr-4">Annuleren</a>
        <input type="submit" value="Uitschrijven" class="btn btn--danger">
    </div>
</form>
@endsection
