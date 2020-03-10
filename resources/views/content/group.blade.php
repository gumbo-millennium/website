@extends('layout.main')

@php
// Set the metadata
SEOMeta::setTitle($page->title);
SEOMeta::setCanonical(route('group.index', ['group' => $page->slug]));
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

{{-- Items --}}

{{-- Content --}}
<div class="container">
    <div class="flex flex-row flex-wrap row">
    @foreach ($pages as $item)
    <article class="col w-full flex-none md:w-1/2 mb-8">
    <div class="shadow rounded bg-white relative">
        <div class="h-64 bg-gray-200" role="presentation">
        @if ($item->image->exists())
            <img class="w-full h-64 object-cover" src="{{ $item->image->url('cover') }}" srcset="{{ $item->image->url('cover') }} 384w,{{ $item->image->url('cover-2x') }} 768w">
        @else
        <div class="w-full h-64 flex items-center">
            <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="h-16 mx-auto">
        </div>
        @endif
        </div>

        <div class="p-8 w-full">
            <p class="mb-0 uppercase font-bold text-sm text-gray-600 leading-none mb-2">{{ $item->category }}</p>
            <h2 class="text-xl font-title mb-4">
                <a href="{{ route('group.show', $item->only('group', 'slug')) }}" class="stretched-link">{{ $item->title }}</a>
            </h2>

            <p>{{ $item->description ?? Str::words(strip_tags($item->html), 10) }}</p>
        </div>
    </div>
    </article>
    @endforeach
</div>
@endsection
