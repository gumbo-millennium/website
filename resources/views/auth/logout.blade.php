@extends('layout.variants.login')

@php
$testUsers = app()->isLocal() ? App\Models\User::where('email', 'LIKE', '%@example.gumbo-millennium.nl')->get() : [];
@endphp

@section('basic-content-small')
{{-- Header --}}
<div class="text-center flex flex-col items-center gap-4">
    <h1 class="login__title">Okay <strong class="login__title-fat">doei</strong></h1>
    <p class="login__subtitle">Je bent succesvol uitgelogd.</p>
</div>

<div class="text-center">
    @if ($next ?? null)
    <div class="grid grid-cols-2 gap-4">
        <a href="/" class="btn">@lang('Homepage')</a>
        <a href="{{ $next }}" class="btn btn--brand">@lang('Continue')</a>
    </div>
    @else
    <a href="/" class="btn btn--brand col-start-2 col-end-4">@lang('Homepage')</a>
    @endif
</div>
@endsection
