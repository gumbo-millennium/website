@extends('layout.default')

@section('content')
    @include('files.bits.header-small')
    @include('files.bits.search-results', [
        'title' => $category->name,
        'items' => $posts,
        'hide' => ['category'],
        'terms' => null
    ])
@endsection
