@extends('layout.default')

@section('content')
{!! !empty(trim($page->post_content_filtered)) ? $page->post_content_filtered : $page->content !!}
@endsection
