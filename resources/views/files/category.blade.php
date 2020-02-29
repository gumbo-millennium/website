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
        @icon('chevron-left', 'mr-2')
        naar overzicht
    </a>
</div>

{{-- Categories --}}
<div class="container">
    @forelse ($bundles as $bundle)
    <div class="flex flex-row p-4 border-gray-300 hover:shadow hover:border-brand-300 border rounded items-center relative mb-4">
        {{-- Get title --}}
        <a href="{{ route('files.show', compact('bundle')) }}" class="flex-grow stretched-link no-underline">
            {{ $bundle->title }}
        </a>

        {{-- Get count --}}
        <p class="p-0 ml-4 text-gray-600 flex-none">{{ $bundle->getMedia()->count() ?? 0 }} bestand(en)</p>
    </div>
    @empty
    <div class="p-8 text-center">
        <h2 class="text-2xl font-title mb-8">Lege categorie</h2>
        <p class="text-lg text-gray-600">Deze categorie bevat geen bundels</p>
    </div>
    @endforelse
</div>
@endsection
