@extends('admin.layout.default')

@php
$greetings = ['Goedenacht', 'Goedemorgen', 'Goedemiddag', 'Goedenavond'];
$greeting = $greetings[floor(now()->format('H') / 6)];
$name = !empty($user->first_name) ? $user->first_name : $user->name;
@endphp

@section('content')

{{-- Tiny boxes of content --}}
<aside class="row mb-3 d-none d-md-flex">
    {{-- File meta --}}
    @include('admin.layout.bits.number-card', [
        'color' => 'brand',
        'number' => $files['count'],
        'change' => max(0, $files['change']),
        'label' => 'Bestanden'
    ])

    {{-- User meta --}}
    @include('admin.layout.bits.number-card', [
        'color' => 'primary',
        'number' => $users['count'],
        'label' => 'Gebruikers'
    ])

    {{-- Join requests --}}
    @include('admin.layout.bits.number-card', [
        'color' => 'primary',
        'number' => $joins['count'],
        'label' => 'Aanmeldingen'
    ])
</aside>

<article>
    <h1 class="h1">{{ $greeting }}</h1>
    <p class="lead">Welkom in het administratiepaneel van Gumbo Millennium.</p>
    <hr class="my-4">
    <p>Dikke kans dat je documenten wilt uploaden, want meer kan je hier niet.</p>
    <a class="btn btn-primary btn-lg" href="{{ route('admin.files.index') }}" role="button">Documenten</a>
</article>
@endsection
