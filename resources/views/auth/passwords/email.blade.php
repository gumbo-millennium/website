@extends('layout.variants.login')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__title">Wachtwoord <strong class="login__title-fat">vergeten</strong>?</h1>
<p class="login__subtitle">Ook wel bekend als "ik heb te lang niet ingelogd".</p>

@if (session('status'))
<div class="notice" role="alert">
    {{ session('status') }}
</div>
@endif

<p>Het overkomt iedereen wel eens, dat je je wachtwoord vergeet.</p>
<p>Maar geen zorgen, tik hieronder gewoon je e-mailadres in, en dan sturen we je een mailtje om je wachtwoord te herstellen.</p>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}

<p class="mt-8 text-gray-600">De link die je ontvang is 1 uur lang geldig.</p>
@endsection
