@extends('layout.default')

@section('content')
{!! $page->post_content_filtered ?? $page->content !!}
@endsection
