@extends('layout.variants.basic')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Jouw <strong>bot koppelingen</strong></h1>
<p class="text-lg text-gray-primary-2 mb-4">Zodat je de site ook kan spammen via Telegram e.d.</p>

<a href="{{ route('account.index') }}" class="w-full block mb-4">« Terug naar overzicht</a>

<p class="leading-loose mb-2">
    Hieronder zie jij je bot-connecties. De berichten die vanaf deze accounts binnenkomen (via bots), worden
    gekoppeld aan jouw Gumbo account. Via deze koppelingen kan je jezelf in- en uitschrijven van activiteiten,
    wist-je-datjes terugtrekken (als je het op tijd doet) en bestanden doorzoeken.
</p>

{{-- Pending quotes --}}
@forelse ($links as $link)
    <div class="p-4 rounded border-brand-secondary-1 flex flex-row items-center">
        @icon("brands/{$link->icon}.svg", "h-16 mr-8 text-brand-primary-1")
        <div class="flex-grow">
            <h3 class="font-bold mb-4 text-brand-primary-1">{{ $link->name }} ({{ $link->driver }})</h3>
            <p>
                Gekoppeld op {{ $link->isoFormat('DDD MMMM YY, HH:mm T') }}
                <a href="{{ route('account.bot-users.unlink', compact('link')) }}">Ontkoppelen</a>
            </p>
        </div>
    </div>
@empty
<div class="text-center p-16">
    <h2 class="text-3xl text-gray-primary-1 text-center">Geen koppelingen</h2>
    <p class="text-lg text-gray-secondary-3 text-center">Praat met een Gumbot om een koppeling te maken</p>
</div>
@endforelse

@endsection
