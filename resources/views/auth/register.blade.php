@extends('layout.variants.login')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Leuk je te <strong class="login__title-fat">ontmoeten</strong></h1>
<p class="login__subtitle">Wat awesome dat je een account wil maken, let's get to it.</p>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}
@endsection
