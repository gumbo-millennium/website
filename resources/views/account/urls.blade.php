@extends('layout.variants.basic')

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
@forelse ($urls as $group => $urls)
<details class="card card--padded">
    <summary class="font-title text-2xl font-light cursor-pointer">{{ $group }}</summary>

    @foreach ($urls as $index => $url)
    <div class="mt-4 pt-4 border-t border-gray-300">
        <label for="{{ $url['id'] }}" class="form__field-label text-bold">{{ $url['title'] }}</label>
        <input class="form__field-input form-input" type="text" value="{{ $url['url'] }}" readonly id="{{ $url['id'] }}">
        @if ($url['expires'] ?? false)
        <p class="form__field-help">Deze URL verloopt {{ $url['expires']->diffForHumans(now(), Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW) }}.</p>
        @endif
    </div>
    @endforeach
</details>
@empty
<div class="notice notice--brand">
    Sorry, het lijkt er op dat je geen bijzondere toegangsrechten hebt.
</div>
@endforelse

@endsection
