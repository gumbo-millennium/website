@extends('layout.variants.basic')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Hallo <strong>{{ $user->first_name }}</strong>,</h1>
<p class="text-lg text-gray-primary-2 mb-4">Beheer je gegevens, of steek alles in de fik en verwijder je account.</p>

{{-- Edit account --}}
<div class="card card--padded">
    <h3 class="heading-3 mt-0">Account bewerken</h3>
    <div class="flex flex-row items-center">
        <p class="leading-none m-0 mr-4 flex-grow">Bewerk je alias of e-mail adres</p>
        <a href="{{ route('account.edit') }}" class="btn btn--brand my-0">Account bewerken</a>
    </div>
</div>

{{-- My grants --}}
<div class="card card--padded">
    <h3 class="heading-3 mt-0">Toestemmingen</h3>
    <div class="flex flex-row items-center">
        <p class="leading-none m-0 mr-4 flex-grow">Jouw gegeven toestemmingen</p>
        <a href="{{ route('account.grants') }}" class="btn my-0">Naar overzicht</a>
    </div>
</div>

{{-- My quotes --}}
<div class="card card--padded">
    <h3 class="heading-3 mt-0">Mijn wist-je-datjes</h3>
    <div class="flex flex-row items-center">
        <p class="leading-none m-0 mr-4 flex-grow">Ingestuurde wist-je-datjes</p>
        <a href="{{ route('account.quotes') }}" class="btn my-0">Naar overzicht</a>
    </div>
</div>

{{-- API toegang --}}
<div class="card card--padded">
    <h3 class="heading-3 mt-0">API toegang</h3>
    <div class="flex flex-row items-center">
        <p class="leading-none m-0 mr-4 flex-grow">Hacken op de Gumbo site</p>
        <a href="{{ route('account.urls') }}" class="btn my-0">Naar overzicht</a>
    </div>
</div>

{{-- API toegang --}}
<div class="card card--padded">
    <h3 class="heading-3 mt-0">Telegram account</h3>
    @if ($telegramName)
    <form action="{{ route('account.tg.unlink') }}" method="POST" class="flex flex-row items-center">
        @csrf
        @method('DELETE')
        <p class="leading-none m-0 mr-4 flex-grow">Gekoppeld aan <strong>{{ $telegramName }}</strong>.</p>
        <button type="submit" class="btn my-0">Loskoppelen</button>
    </form>
    @else
    <div class="flex flex-row items-center">
        <p class="leading-none m-0 mr-4 flex-grow">Je hebt geen Telegram account gekoppeld.</p>
    </div>
    @endif
</div>
@endsection
