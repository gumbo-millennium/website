@extends('layout.main')

@section('content')
<h1>Bestandensysteem - {{ $file->title }}</h1>

<p>Dit is een bestand</p>

<a href="{{ route('files.download', compact('file')) }}">Downloaden</a><br />
<a href="{{ route('files.category', ['category' => $file->category]) }}">Terug naar categorie</a>
@endsection
