@extends('layout.main')

@section('content')
{{-- Header --}}
<div class="container">
    <div class="page-hero">
        <h1 class="page-hero__title">{{ $category->title }}</h1>
    </div>
</div>

{{-- Back link --}}
<div class="container">
    <a href="{{ route('files.index') }}" class="inline-block p-4 mb-4 no-underline p-4">
        @icon('solid/chevron-left', 'mr-2')
        naar overzicht
    </a>
</div>

{{-- Categories --}}
<div class="container file-set file-set--inline">
    @forelse ($bundles as $bundle)
    <div class="file-set__item">
        {{-- Get title --}}
        <a href="{{ route('files.show', compact('bundle')) }}" class="file-set__item-title">
            {{ $bundle->title }}
        </a>

        {{-- Get count --}}
        <p class="file-set__item-meta">{{ $bundle->getMedia()->count() ?? 0 }} bestand(en)</p>
    </div>
    @empty
    <div class="file-set__empty-notice">
        <h2 class="file-set__empty-notice-title">Lege categorie</h2>
        <p class="file-set__empty-notice-body">Deze categorie bevat geen bundels</p>
    </div>
    @endforelse
</div>
@endsection
