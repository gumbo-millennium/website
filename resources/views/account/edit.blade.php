@extends('layout.variants.basic')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Account <strong>bewerken</strong></h1>
<p class="text-lg text-gray-700 mb-4">Verander je alias of update je e-mailadres.</p>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}
@endsection
