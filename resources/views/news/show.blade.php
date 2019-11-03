@extends('layout')

@section('content')
<h1>Nieuws - {{ $item->title }}</h1>

{!! $item->html !!}
@endsection
