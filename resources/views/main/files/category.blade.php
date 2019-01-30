@extends('main.layout.default')

@section('content')
    @include('main.files.bits.header-small')
    @include('main.files.bits.search-results', [
        'title' => $category->title,
        'items' => $files,
        'hide' => ['category'],
        'terms' => null
    ])
@endsection
