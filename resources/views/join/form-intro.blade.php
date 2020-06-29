@extends('layout.variants.login')

@php
$endFormat = 'dddd D MMMM';
$startFormat = $intro->start_date->month !== $intro->end_date->month ? $endFormat : 'dddd D';
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Mee met de <strong>introductieweek</strong>?</h1>
<p class="login__subtitle">Superleuk dat je mee wilt met de introductieweek. Vul allereerst hieronder je gegevens in.</p>

{{-- Add errors --}}
@include('join.partials.form-errors')

{{-- Add intro --}}
<div class="mb-4">
    <p class="leading-relaxed mb-2">
        Zie jij het wel zitten om van {{ $intro->start_date->isoFormat($startFormat) }} t/m {{ $intro->end_date->isoFormat($endFormat) }} mee te doen met de introdutieweek
        van Gumbo Millennium? Vul dan hieronder je persoonsgegevens in.
    </p>
    <p class="mb-2 leading-relaxed">
        Om het voor jou en ons zo makkelijk mogelijk te maken, vragen wij je om direct de kosten voor deelname (á {{ Str::price($intro->total_price) }}) te betalen.
        Indien je dit niet doet binnen 7 dagen, zullen we je inschrijving weer verwijderen (om je gegevens te beschermen).
    </p>
    @include('join.partials.data-safety')
</div>

{{-- Add form --}}
@include('join.partials.form')

@endsection
