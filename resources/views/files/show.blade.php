@extends('layout.main')

@php
$description = $bundle->description;
if (empty($description)) {
    $description = 'Er is geen omschrijving voor deze bundel.';
}

$properties = [
    'Auteur' => optional($bundle->user)->public_name ?? 'Onbekend',
    'Downloads' => $bundle->downloads_count,
    'Totale grootte' => Str::filesize($bundle->total_size),
    'Aangemaakt op' => $bundle->created_at->isoFormat('D MMM Y, HH:mm (z)'),
    'Gepubliceerd op' => $bundle->published_at->isoFormat('D MMM Y, HH:mm (z)'),
];
@endphp

@push('files.download-class', 'btn ')
@push('files.download-class', $bundleMedia->isEmpty() ? 'btn--disabled' : 'btn--brand')

@section('content')
<article class="container pt-12">
    {{-- Create two-section grid --}}
    <div class="flex row flex-col lg:flex-row-reverse">
        {{-- Group metadata --}}
        <div class="col w-full lg:w-5/12 lg:flex-none">
            {{-- File name --}}
            <h1 class="text-2xl font-title mb-4">{{ $bundle->title }}</h1>

            {{-- Description --}}
            <p class="text-gray-600 mb-4">{{ $description }}</p>

            {{-- Download all --}}
            <a class="@stack('files.download-class')" href="{{ route('files.download', compact('bundle')) }}">Alles downloaden</a>

            {{-- Data --}}
            <dl class="my-8 py-8 border-gray-300 border-t border-b flex flex-row flex-wrap row">
                @foreach ($properties as $label => $value)
                <dt class="col w-1/3 flex-none mb-2 font-bold">{{ $label }}</dt>
                <dd class="col w-2/3 flex-none mb-2 text-sm font-gray-600">{{ $value }}</dd>
                @endforeach
            </dl>

            {{-- Back link --}}
            <a href="{{ route('files.category', ['category' => $bundle->category]) }}" class="inline-block p-4 mb-4 no-underline p-4 text-sm">
                    @icon('chevron-left', 'mr-2')
                    Terug naar {{ $bundle->category->title }}
                </a>
        </div>

        {{-- Files --}}
        <div class="col w-full lg:w-7/12 lg:flex-none">
            {{-- Files --}}
            @forelse ($bundleMedia as $file)
            <div class="flex flex-row p-4 border-gray-300 hover:shadow hover:border-brand-300 border rounded items-center relative mb-4">
                {{-- Get icon --}}
                @include('files.bits.icon', compact('file'))

                {{-- Get title --}}
                <a href="{{ route('files.download-single', ['media' => $file]) }}" class="flex-grow stretched-link no-underline">
                    {{ $file->name }}
                </a>

                {{-- File downloads --}}
                <p class="p-0 ml-4 text-gray-600 flex-none">{{ $file->downloads_count }} downloads</p>

                {{-- Get size --}}
                <p class="p-0 ml-4 text-gray-600 flex-none">{{ Str::filesize($file->size) }}</p>
            </div>
            @empty
            <div class="p-16 text-center">
                <h2 class="text-2xl font-title mb-8">Lege bundel</h2>
                <p class="text-lg text-gray-600">Deze bundel is leeg</p>
            </div>
            @endforelse
        </div>
    </div>
</article>
@endsection
