@extends('layout.variants.basic')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Verbinden met <strong>Telegram</strong></h1>
<p class="text-lg text-gray-600 mb-4">Om besloten activiteiten en de plazacam via de bot op te vragen, moet je je Telegram account aan je Gumbo account linken.</p>

{{-- Display --}}
<div class="flex flex-row my-16 md:mx-16">
    {{-- Telegram user --}}
    <div class="flex flex-col items-center w-1/3 flex-grow">
        <div class="h-20 w-20 rounded-full bg-blue-500 text-white mb-4 flex items-center justify-center">
            <x-icon icon="brands/telegram-plane" class="h-8" />
        </div>

        <strong class="text-lg">{{ $telegramName }}</strong>
        <p class="text-sm text-grey-secondary-3">#{{ $telegramId }}</p>
    </div>

    {{-- Arrow --}}
    <div class="flex flex-col items-center w-20 flex-none">
        <x-icon icon="solid/arrow-right" class="text-grey-primary-3 h-8 mt-6" />
    </div>

    {{-- Gumbo user --}}
    <div class="flex flex-col items-center w-1/3 flex-grow">
        <div class="h-20 w-20 rounded-full bg-brand-500 text-white mb-4 flex items-center justify-center">
            <x-icon icon="solid/user" class="h-8" />
        </div>

        <strong class="text-lg">{{ $user->first_name }}</strong>
    </div>
</div>

<form class="flex flex-row justify-end" method="POST" action="{{ route('account.tg.link') }}">
    @csrf
        <a href="{{ route('account.index') }}" class="btn btn--secondary btn--small mr-4">Annuleren</a>
        <button class="btn btn--brand btn--small">Koppelen</button>
</form>
@endsection
