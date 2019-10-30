@extends('layout')

@section('content')
<h1>Bestandensysteem - Home</h1>

<p>Kies een categorie om door te gaan</p>

<ul>
    @foreach ($categories as $category)
    <li>
        <a href="{{ route('files.category', compact('category')) }}">{{ $category->title }}</a><br />
        <p>{{ Str::number($category->file_count) ?? 'geen' }} bestand(en)</p>
    </li>
    @endforeach
</ul>
@endsection
