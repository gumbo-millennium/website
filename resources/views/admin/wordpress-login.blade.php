@extends('admin.layout.default')

@section('content')

@php
$boxes = [
    ['brand', $count['pages'] ?? 0, 'Pagina\'s'],
    ['info', $count['posts'] ?? 0, 'Nieuwsitems'],
    ['danger', $count['media'] ?? 0, 'Media bestanden'],
    ['primary', $count['users'] ?? 0, 'Gebruikers']
];
@endphp

{{-- Tiny boxes of content --}}
<aside class="row mb-3">
    {{-- Boxes --}}
    @foreach ($boxes as list($color, $value, $title))
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="number-card number-card--{{ $color }}-outline">
            <div class="number-card__number">
                {{ $value }}
            </div>
            <p class="number-card__description">{{ $title }}</p>
        </div>
    </div>
    @endforeach
</aside>

<form class="jumbotron" href="{{ route('admin.wordpress') }}" method="post">
    @csrf
    <h1 class="display-4">Inloggen bij WordPress</h1>
    <p class="lead">Klik op onderstaande knop om naar de WordPress omgeving te gaan.</p>
    <hr class="my-4">
    <p>Je moet hier opnieuw inloggen. Hiervoor kan je de volgende credenials gebruiken:</p>
    <dl class="row">
        {{-- Username --}}
        <dt class="col-xs-6 col-sm-4 col-md-3">Gebruikersnaam</dt>
        <dd class="col-xs-6 col-sm-8 col-md-9">{{ optional($user->wordpress)->login ?? $user->email }}</dd>

        {{-- Password --}}
        <dt class="col-xs-6 col-sm-4 col-md-3">Wachtwoord</dt>
        <dd class="col-xs-6 col-sm-8 col-md-9"><em>Je account wachtwoord</em></dd>
    <dl>
    <button type="submit" name="scope" value="user" class="btn btn-primary btn-lg" role="button">Inloggen</a>
</form>
@endsection
