@extends('layout.default')

@push('layout.content-before')
<div class="hero hero--blog">
    <div class="hero__container">
        <h3 class="hero__header">
            {{ $post->title }}
        </h3>
    </div>
</div>
@endpush

@section('content')
{!! $post->post_content_filtered ?? $post->content !!}
@endsection
