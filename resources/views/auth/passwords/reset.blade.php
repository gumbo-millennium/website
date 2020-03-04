@extends('layout.variants.login')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Wachtwoord <strong class="login__title-fat">herstellen</strong></h1>
<p class="login__subtitle">Je bent nog maar 2 × 10 tekens verwijderd van een nieuw wachtwoord.</p>

<p>Je hebt het zware werk al gehad. Kijk even na of je e-mailadres klopt.</p>

{{-- Render form --}}
{!! form_start($form, ['class' => 'form']) !!}
{!! form_until($form, 'email') !!}

<p class="mt-8">Als je e-mailadres klopt, tik dan hieronder 2x hetzelfde wachtwoord in van <strong>minimaal 10 tekens</strong></p>

{!! form_end($form) !!}
@endsection
