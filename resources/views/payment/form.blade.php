@extends('layout')

@section('title', "Inschrijving betalen - {{ $activity->name }} - Gumbo Millennium")

@section('content')
<h1>{{ $activity->name }} - Betaling</h1>

<p>Om je inschrijving voor {{ $activity->title }} af te ronden, dien je {{ Str::price( $enrollment->price / 100 ) }} te betalen.</p>
<p>Al onze betalingen lopen via iDEAL. Wil je niet betalen via iDEAL of wil je een betalingsregeling treffen, neem dan contact op met het bestuur.</p>

@if (!$enrollment->state->isStable())
<div class="my-2 px-4 py-2 bg-red-100 text-red-800 border rounded border-red-600 inline-block">
    <h3 class="tex-2xl text-red-600">Let op</h3>
    <p>Je inschrijving verloopt over {{ $enrollment->expire->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}.</p>
    <p>Indien je een betalingsregeling wilt treffen, dien je voor {{ $enrollment->expire->isoFormat('D MMMM YYYY, HH:mm') }} goedkeuring hiervoor
        gekregen hebben van het bestuur.</p>
</div>
@endif

<form action="{{ route('payment.start', compact('activity')) }}" method="post">
@csrf
<label for="bank">Bank</label>
<select name="bank" id="bank">
@foreach ($banks as $bank => $bankName)
<option value="{{ $bank }}">{{ $bankName }}</option>
@endforeach
</select>

<label>
    <input type="checkbox" name="accept" required />
    Ik ga akkoord met de voorwaarden voor betalingen via de Gumbo Website.
</label>

<input type="submit" value="Ok">
</form>

@endsection
