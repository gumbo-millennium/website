@extends('layout.main')

@section('title', "Inschrijving annuleren - {{ $activity->name }} - Gumbo Millennium")

@section('content')
<h1>{{ $activity->name }} - Annuleren</h1>

<p>Klik hieronder om je uit te schrijven voor {{ $activity->title }}.</p>

@if ($enrollment->state->name === 'Paid')
<p class="py-2 px-4 text-blue-800">Het geld wordt binnen 3 werkdagen automatisch teruggestort op je bankrekening.
@endif

<form action="{{ route('enroll.delete', compact('activity')) }}" method="post">
@method('DELETE')
@csrf

@if ($enrollment->state->name === 'Paid')
<div class="my-2 p-4 flex flex-row items-center">
    <input type="checkbox" name="accept" id="accept" required>
    <label for="accept" class="px-2">Ik ga akkoord dat ik, na uitschrijving, mij <strong>niet meer via de website kan inschrijven</strong>.</label>
</div>
@else
<input type="hidden" name="accept" value="1">
@endif

<input type="submit" value="Uitschrijven" class="cursor-pointer rounded px-4 py-2 text-white bg-red-800 shadow">
</form>

@endsection
