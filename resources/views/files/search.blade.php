@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    {{-- Title --}}
    <div class="page-hero">
        <h1 class="page-hero__title">Zoekresultaten voor “{{ $searchQuery }}”</h1>
    </div>
</div>

{{-- Search and count --}}
<div class="container mb-4">
    <form action="{{ route('files.search') }}" method="GET" class="flex flex-col md:flex-row">
        {{-- Search --}}
        <input type="search" name="query" placeholder="Doorzoek de bestanden"
            class="form-input px-6 py-4 md:mr-4 md:flex-grow md:w-full" value="{{ $searchQuery }}">

        {{-- Submit --}}
        <button class="px-6 py-4 form-input md:flex-none" type="submit">Zoeken</button>
    </form>

    {{-- Spacer --}}
    <div class="container my-8 border-b border-gray-200"></div>
</div>

{{-- Categories --}}
<div class="container file-set file-set--inline">
    @if ($files->isEmpty())
    <div class="file-set__empty-notice">
        <h2 class="file-set__empty-notice-title">Geen resultaten</h2>
        <p class="file-set__empty-notice-body">Sorry, je zoekopdracht leverde geen resultaten op.</p>
    </div>
    @else
    {{-- Count --}}
    <p class="mt-2 px-1">
        <strong>{{ $files->total() }}</strong> resultaten voor “{{ $searchQuery }}”.
    </p>

    @foreach ($files as $file)
    <div class="file-set__item">
        {{-- Get title --}}
        <a href="{{ route('files.show', ['bundle' => $file->bundle]) }}" class="file-set__item-title">
            <strong>{{ $file->name }}</strong> in {{ $file->bundle->title }}
        </a>

        {{-- Get count --}}
        <p class="file-set__item-meta flex flex-row items-center text-gray-600">
            {{ $file->bundle->category->title }}
        </p>
    </div>
    @endforeach

    {{-- Spacer --}}
    <div class="my-8 border-b border-gray-200"></div>

    <div class="flex flex-row items-center">
        {{-- Back link --}}
        @if ($files->previousPageUrl())
        <a href="{{ $files->previousPageUrl() }}" class="btn btn--link">Vorige</a>
        @endif

        {{-- Spacer --}}
        <div class="mx-auto">Pagina {{ $files->currentPage() }} van {{ $files->lastPage() }}</div>

        {{-- Next link --}}
        @if ($files->hasMorePages())
            <a href="{{ $files->nextPageUrl() }}" class="btn btn--brand">Volgende</a>
        @endif
    </div>
    @endif
</div>
@endsection
