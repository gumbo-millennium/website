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
    <div class="card-grid">
    @foreach ($items as $item)
    @php
    $postTimestamp = $item->published_at ?? $item->created_at;
    $postIso = $postTimestamp->toIso8601String();
    $postDate = $postTimestamp->isoFormat('DD MMM \'YY');
    $headline = $item->headline ?? Str::words(strip_tags($item->html), 10);
    @endphp
    <article class="card-grid__item">
        <div class="card">
            <div class="card__figure" role="presentation">
            @if ($item->image->exists())
                <img class="card__figure-image" src="{{ $item->image->url('cover') }}" srcset="{{ $item->image->url('cover') }} 384w,{{ $item->image->url('cover-2x') }} 768w">
            @else
                <div class="card__figure-wrapper">
                    <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="h-16 mx-auto">
                </div>
            @endif
            </div>

            <div class="card__body">
                <p class="card__body-label">{{ $item->category }}</p>
                <h2 class="card__body-title">
                    <a href="{{ route('news.show', ['news' => $item]) }}" class="stretched-link">{{ $item->title }}</a>
                </h2>

                <p class="card__body-content">{{ $headline }}</p>
                <time datetime="{{ $postIso }}" class="card__body-meta">{{ $postDate }}</time>
            </div>
        </div>
    </article>
    @endforeach
</div>
@endsection
