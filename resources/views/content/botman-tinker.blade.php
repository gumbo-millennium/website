@extends('layout.main')

@section('title', "Bots test page")

@section('content')
<article class="container py-8">
    {{-- Replaced by Vue --}}
    <div data-content="bot-tinker" data-api-endpoint="{{ route('api.botman', null, false) }}"></div>
</article>
@endsection
