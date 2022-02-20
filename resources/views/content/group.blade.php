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
        <div class="prose">
            {!! $page->html !!}
        </div>
    </div>
</article>

{{-- Items --}}

{{-- Content --}}
<div class="container">
    <div class="flex flex-row flex-wrap row">
    {{-- Add disclaimer --}}
    @includeWhen($group === 'coronavirus', 'covid19.disclaimer-card')

    {{-- Add all pages --}}
    @foreach ($pages as $item)
    @php
    $bannerImage = image_asset($item->cover)->preset('banner');
    $bannerImage2x = (clone $bannerImage)->dpr(2);
    @endphp
    <article class="col w-full flex-none md:w-1/2 mb-8">
    <div class="card">
        <div class="card__figure" role="presentation">
        @if ($item->cover)
            <img class="card__figure-image" src="{{ $bannerImage }}" srcset="{{ $bannerImage }} 384w,{{ $bannerImage2x }} 768w">
        @else
        <div class="card__figure-wrapper">
            <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="h-16 mx-auto block dark:hidden">
            <img src="{{ mix('images/logo-text-night.svg') }}" alt="Gumbo Millennium" class="h-16 mx-auto hidden dark:block">
        </div>
        @endif
        </div>

        <div class="card__body">
            <p class="card__body-label">{{ $item->category }}</p>

            <h2 class="card__body-title">
                <a href="{{ route('group.show', $item->only('group', 'slug')) }}" class="stretched-link">{{ $item->title }}</a>
            </h2>

            <p class="card__body-content">{{ $item->description ?? Str::words(strip_tags($item->html), 10) }}</p>
        </div>
    </div>
    </article>
    @endforeach
</div>
@endsection
