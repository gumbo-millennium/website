@extends('layout.variants.basic')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Account <strong>bewerken</strong></h1>
<p class="text-lg text-gray-700 mb-4">Soms wil je gewoon iemand anders zijn, dat kan.</p>

<p>
    Je kan hieronder de gegevens aanpassen. Indien je een actief lidmaatschap hebt, kan je je naam niet aanpassen.
</p>

{{-- Show locked notice --}}
@if ($isLinked ?? false)
<div class="notice notice--brand">
    <p>
        We hebben je naam uit de ledenadministratie opgehaald.<br />
        <span class="text-sm">Kloppen deze gegevens niet? Neem dan contact op met het bestuur.</span>
    </p>
</div>
@endif

{{-- Render form --}}
{!! form_start($form, ['class' => 'form']) !!}
{!! form_until($form, 'after_name') !!}

<hr class="border-gray-300 my-4" />

<h3 class="text-xl font-normal">Profiel informatie</h3>
<p class="mb-8">Pas hieronder je e-mailadres en alias aan. Het wijzigen van je e-mailadres blokkeert je account totdat je deze valideert.</p>

{{-- {!! form_rest($form) !!} --}}
{!! form_end($form) !!}
@endsection
