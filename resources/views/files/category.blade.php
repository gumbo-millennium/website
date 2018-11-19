@extends('layout.default')

@section('content')
    @include('files.bits.header-small')
    @include('files.bits.search-results', [
        'title' => $category->title,
        'items' => $files,
        'hide' => ['category'],
        'terms' => null
    ])
@endsection
