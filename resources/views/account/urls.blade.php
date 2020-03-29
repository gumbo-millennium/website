@extends('layout.variants.basic')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Time to become <strong>hackerman</strong></h1>
<p class="text-lg text-gray-primary-2 mb-4">Maak wat je wil, samen met de informatie van Gumbo.</p>

<a href="{{ route('account.index') }}" class="w-full block mb-4">« Terug naar overzicht</a>

<p class="leading-loose mb-2">
    De URLs hieronder bieden je toegang tot bepaalde API-endpoints. Leef je uit met de informatie die
    je hier vind, maar houd rekening met de eventuele doelgroep van de informatie.
</p>
<p class="leading-loose mb-4">
    Let op: Sommige URLs hebben een verlooptermijn.
</p>

{{-- Edit account --}}
<div class="form">
    @forelse ($urls as $index => $url)
    <div class="form__field">
        <label for="url-{{ $index }}" class="form__field-label text-bold">{{ $url['title'] }}</label>
        <input class="form__field-input form-input" type="text" value="{{ $url['url'] }}" readonly id="url-{{ $index }}">
        @if ($url['expires'] ?? false)
            <p class="form__field-help">Deze URL verloopt over een jaar.</p>
        @endif
    </div>
    @empty
    <div class="notice notice--brand">
        Sorry, het lijkt er op dat je geen bijzondere toegangsrechten hebt.
    </div>
    @endforelse
</div>

@endsection
