@extends('admin.layout.default')

@php
$greetings = ['Goedenacht', 'Goedemorgen', 'Goedemiddag', 'Goedenavond'];
$greeting = $greetings[floor(now()->format('H') / 6)];
$name = !empty($user->first_name) ? $user->first_name : $user->display_name;
@endphp

@section('layout.content')
<main class="container">
    <h1 class="display-4">{{ $greeting }} {{ $name }}!</h1>
</main>
@endsection
