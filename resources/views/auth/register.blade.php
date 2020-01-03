@extends('layout.variants.login')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('login-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Leuk je te <strong>ontmoeten</strong></h1>
<p class="text-lg text-gray-700 mb-4">Account nodig voor de site? Meld je dan snel aan met onderstaand formulier.</p>

<p class="mb-8 text-gray-700 text-sm">
    Je persoonsgegevens worden behandeld volgens het <a href="/online-privacy-policy">Online Privacybeleid</a> van
    Gumbo Millennium.
</p>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}
@endsection
