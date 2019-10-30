@extends('layout')

@section('content')
<h1>Bestandensysteem - {{ $category->title }}</h1>

<p>Kies een categorie om door te gaan</p>

<ul>
    @foreach ($files as $file)
    <li>
        <a href="{{ route('files.show', compact('file')) }}">{{ $file->title }}</a><br />
        <p>{{ $file->created_at->isoFormat('D MMMM YYYY, HH:mm') }}</p>
    </li>
    @endforeach
</ul>
@endsection
