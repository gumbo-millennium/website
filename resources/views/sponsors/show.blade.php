@extends('layout.main')

@section('content')
<div class="sponsor-single">
    <div class="container container--md">
        <a href="{{ route('sponsors.index') }}" class="sponsor-single__back" rel="parent">
            @icon('chevron-left', 'h-4 mr-2')
            <span>Terug naar sponsoren</span>
        </a>

        <article class="sponsor-single__container">

            {{-- Header --}}
            <header class="sponsor-single__header">

                {{-- Title bit --}}
                <div class="sponsor-single__header-text">
                    <h1 class="sponsor-single__header-title">{{ $sponsor->contents_title ?? $sponsor->name }}</h1>
                    <p class="sponsor-single__header-subtitle">
                        @icon('ad', 'h-4 mr-2')
                        <span>Gesponsord door {{ $sponsor->name }}</span>
                    </p>
                </div>

                {{-- Button bit --}}
                <a href="{{ route('sponsors.link', compact('sponsor')) }}" target="_blank" rel="noopener" class="btn btn--brand sponsor-single__header-button">
                    Lees meer
                </a>
            </header>

            {{-- Get content --}}
            <div class="sponsor-single__content plain-content">
                {!! $sponsor->content_html !!}
            </div>

            {{-- Call-to-action at the bottom --}}
            <div class="sponsor-single__footer">
                <span class="sponsor-single__footer-text">
                    Interesse in {{ $sponsor->name }}?
                </span>
                <a href="{{ route('sponsors.link', compact('sponsor')) }}" target="_blank" rel="noopener" class="btn btn--brand sponsor-single__footer-button">
                    Lees meer
                </a>
            </div>
        </article>
    </div>
</div>
@endsection
