@extends('layout.main')

@section('title', "Doorverwijzen naar betalingsprovider - Gumbo Millennium")

@section('main.scripts', '')
@section('main.header', '')
@section('main.footer', '')

@section('content')
<div class="container container-sm my-32">
    <div class="mb-8" role="presentation">
        <img src="{{ mix('/images/logo-text-green.svg') }}" alt="Gumbo Millennium"
            class="mx-auto" width="250" height="100" />
    </div>

    <p class="text-xl leading-relaxed text-center mb-8">
        @if (!empty($message))
        {{ $message }} Een ogenblik geduld...
        @else
        De betaling wordt gestart. Een ogenblik geduld...
        @endif
    </p>

    <p class="text-gray-600 text-center text-sm">Indien dit te lang duurt, zullen we je op een later moment mailen.</p>
</div>
@endsection
