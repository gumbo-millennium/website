@extends('admin.layout.default')

@php
$greetings = ['Goedenacht', 'Goedemorgen', 'Goedemiddag', 'Goedenavond'];
$greeting = $greetings[floor(now()->format('H') / 6)];
$name = !empty($user->first_name) ? $user->first_name : $user->display_name;
@endphp

@section('content')

{{-- Tiny boxes of content --}}
<aside class="row mb-3 d-none d-md-flex">
    {{-- File meta --}}
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="number-card number-card--brand-outline">
            <div class="number-card__number">
                {{ $files['count'] }}
                @if ($files['change'] > 0)
                <span class="number-card__indicator number-card__indicator--positive">{{ sprintf('%.0f%%', $files['change']) }}</span>
                @endif
            </div>
            <p class="number-card__description">Bestand(en)</p>
        </div>
    </div>

    {{-- User meta --}}
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="number-card number-card--primary-outline">
            <div class="number-card__number">
                {{ $users['count'] }}
            </div>
            <p class="number-card__description">Gebruiker(s)</p>
        </div>
    </div>

    {{-- Join requests --}}
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="number-card number-card--primary-outline">
            <div class="number-card__number">
                {{ $joins['count'] }}
            </div>
            <p class="number-card__description" title="Verzoeken tot lidmaatschap">Verzoeken</p>
        </div>
    </div>
</aside>

<div class="jumbotron">
    <h1 class="display-4">{{ $greeting }} {{ $name }}!</h1>
    <p class="lead">Welkom in het administratiepaneel van Gumbo Millennium.</p>
    <hr class="my-4">
    <p>Dikke kans dat je documenten wilt uploaden, want meer kan je hier niet.</p>
    <a class="btn btn-primary btn-lg" href="{{ route('admin.files.index') }}" role="button">Documenten</a>
</div>
@endsection
