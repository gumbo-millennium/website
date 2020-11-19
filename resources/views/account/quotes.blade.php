@extends('layout.variants.basic')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Jouw <strong>wist-je-datjes</strong></h1>
<p class="text-lg text-gray-primary-2 mb-4">Telt het als klikken als anderen er plezier aan beleven?</p>

<a href="{{ route('account.index') }}" class="w-full block mb-4">« Terug naar overzicht</a>

<p class="leading-loose mb-2">
    Hieronder zie je de nog-te-versturen wist-je-datjes (waar je eventueel zelfcensuur op kan loslaten), en
    een beperkte set verstuurde wist-je-datjes.
</p>

{{-- Deletion form --}}
<form name="quote-delete" id="quote-delete" class="hidden" aria-hidden="true" action="{{ route('account.quotes.delete') }}" method="POST">
    @csrf
    @method('DELETE')
</form>

{{-- Pending quotes --}}
<h3 class="font-title text-2xl">Te-verzenden wist-je-datjes</h3>
<p class="mb-4">Deze wist-je-datjes moeten nog verstuurd worden. Hier kàn je nog een potje zelfcensuur op loslaten</p>

@component('account.bits.quote-grid', ['delete' => true, 'quotes' => $unsent])
@slot('empty')
<div class="p-16 text-center">
    <h3 class="text-title text-center">Geen wist-je-datjes</h3>
    <p class="text-gray-primary-2">Je hebt nog geen wist-je-datjes ingestuurd, of ze zijn allemaal al doorgestuurd.</p>
</div>
@endslot
@endcomponent

<hr class="my-8 border-gray-secondary-3" />

{{-- Pending quotes --}}
<h3 class="font-title text-2xl">Verzonden wist-je-datjes</h3>
<p class="mb-4">Deze wist-je-datjes zijn doorgestuurd naar de Gumbode. Oude wist-je-datjes kunnen verwijderd worden.</p>

@component('account.bits.quote-grid', ['delete' => false, 'quotes' => $sent])
@slot('empty')
<div class="p-16 text-center">
    <h3 class="text-title text-center">Geen wist-je-datjes</h3>
    <p class="text-gray-primary-2">Er zijn geen wist-je-datjes van jou doorgestuurd naar de Gumbode, of ze zijn verwijderd.</p>
</div>
@endslot
@endcomponent


@endsection
