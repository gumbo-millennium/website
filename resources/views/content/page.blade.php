@extends('layout.main')

@php
// Set the metadata
SEOMeta::setTitle($page->title);
SEOMeta::setCanonical($page->url);
@endphp

@section('content')
{{-- All in an article --}}
<article>
    {{-- Header --}}
    <div class="container">
        <div class="page-hero">
            <h1 class="page-hero__title">{{ $page->title }}</h1>
            @if (!empty($page->tagline))
            <p class="page-hero__lead">{{ $page->tagline }}</p>
            @endif
        </div>
    </div>

    {{-- Contents --}}
    <div class="container">
        <div class="plain-content">
            {!! $page->html !!}
        </div>
    </div>
</article>
@endsection
