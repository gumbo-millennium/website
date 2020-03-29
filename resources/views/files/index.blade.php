@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">Bestandensysteem</h1>
        <p class="page-hero__lead">De officiële documenten van Gumbo Millennium, speciaal voor leden.</p>
    </div>
</div>

{{-- Categories --}}
<div class="container">
    @foreach ($categories as $category)
    @php
    $bundles = $category->bundles()->whereAvailable()->take(3)->get();
    @endphp
    <div class="file-set">
        {{-- Get label --}}
        <a href="{{ route('files.category', compact('category')) }}" class="file-set__title">{{ $category->title }}</a>

        {{-- Files --}}
        @if ($bundles)
            @foreach ($bundles as $bundle)
            <div class="file-set__item">
                {{-- Get title --}}
                <a href="{{ route('files.show', compact('bundle')) }}" class="file-set__item-title stretched-link">
                    {{ $bundle->title }}
                </a>

                {{-- Get count --}}
                <p class="file-set__item-meta">{{ $bundle->getMedia()->count() ?? 0 }} bestand(en)</p>
            </div>
            @endforeach

            {{-- Show all link --}}
            <a href="{{ route('files.category', compact('category')) }}" class="file-set__show-all">
                Bekijk alle bundels in {{ $category->title }}
                @icon('chevron-right', 'ml-2')
            </a>
        @else
        <div class="file-set__empty-notice">
            <h2 class="file-set__empty-notice-title">Lege categorie</h2>
            <p class="file-set__empty-notice-body">Deze categorie bevat geen bundels</p>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection
