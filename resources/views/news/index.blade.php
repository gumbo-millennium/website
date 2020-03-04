@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">Nieuws</h1>
        <p class="page-hero__lead">Het laatste nieuws van Gumbo Millennium</p>
    </div>
</div>

{{-- Content --}}
<div class="container">
    <div class="flex flex-row flex-wrap row">
    @foreach ($items as $item)
    @php
    $postTimestamp = $item->published_at ?? $item->created_at;
    $postIso = $postTimestamp->toIso8601String();
    $postDate = $postTimestamp->isoFormat('DD MMM \'YY');
    @endphp
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
            <h2 class="text-xl font-title mb-21">
                <a href="{{ route('news.show', ['news' => $item]) }}" class="stretched-link">{{ $item->title }}</a>
            </h2>

            <p class="mb-4">{{ Str::words(strip_tags($item->html), 10) }}</p>
            <time datetime="{{ $postIso }}" class="text-right text-gray-600">{{ $postDate }}</time>
        </div>
    </div>
    </article>
    @endforeach
</div>
@endsection
