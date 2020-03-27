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

    @if ($page->group === 'coronavirus')
    <div class="container">
        <div class="notice notice--large notice--info">
            <strong class="notice__title">Alleen officiële informatie</strong>
            <p class="m-0 w-full">
                De informatie in dit artikel is een uitspraak van ons als vereniging.
                Voor actuele informatie, adviezen en cijfers raden wij je altijd aan
                om te kijken naar de officiële instanties.
            </p>
            @include('covid19.block')
        </div>
    </div>
    @endif

    {{-- Contents --}}
    <div class="container">
        <div class="plain-content">
            {!! $page->html !!}
        </div>
    </div>
</article>
@endsection
