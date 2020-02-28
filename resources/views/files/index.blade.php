@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">Bestandensysteem</h1>
        <p class="page-hero__lead">De officiële documenten van Gumbo Millennium, speciaal voor leden.</p>
    </div>
</div>

<div class="container after-header">
    <p class="text-lg mb-4">Kies een categorie om verder te gaan</p>
    <div class="file-categories">
        @foreach ($categories as $category)
        <div class="file-categories__category">
            <div class="file-categories__category-inner">
                <a class="no-underline" href="{{ route('files.category', compact('category')) }}">
                    <h3 class="text-2xl font-normal underline">{{ $category->title }}</h3>
                    <p>{{ $category->files_count }} bestand(en)</p>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
