@extends('layout.main')

@php
$variants = [
    'bg-blue-200',
    'bg-green-200',
    'bg-orange-200',
    'bg-red-200'
];
$getVariant = static fn ($index) => $variants[$index % count($variants)];
@endphp

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
    <div class="mb-12">
        {{-- Get label --}}
        <a href="{{ route('files.category', compact('category')) }}" class="inline-block p-2 px-4 {{ $getVariant($loop->index) }} no-underline mb-4 rounded-full">{{ $category->title }}</a>

        {{-- Files --}}
        @if ($bundles)
            @foreach ($bundles as $bundle)
            <div class="flex flex-row p-4 border-gray-300 hover:shadow hover:border-brand-300 border rounded items-center relative mb-4">
                {{-- Get title --}}
                <a href="{{ route('files.show', compact('bundle')) }}" class="flex-grow stretched-link no-underline">
                    {{ $bundle->title }}
                </a>

                {{-- Get count --}}
                <p class="p-0 ml-4 text-gray-600 flex-none">{{ $bundle->getMedia()->count() ?? 0 }} bestand(en)</p>
            </div>
            @endforeach

            {{-- Show all link --}}
            <a href="{{ route('files.category', compact('category')) }}" class="block flex-grow no-underline p-4">
                Bekijk alle bundels in {{ $category->title }}
                @icon('chevron-right', 'ml-2')
            </a>
        @else
        <div class="p-8 text-left">
            <h2 class="text-xl font-title mb-8">Lege categorie</h2>
            <p class="text-lg text-gray-600">Deze categorie bevat geen bundels</p>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection
