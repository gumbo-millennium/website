@extends('layout.variants.two-col')

@php
$description = $bundle->description;
if (empty($description)) {
    $description = 'Er is geen omschrijving voor deze bundel.';
}

$properties = [
    'Auteur' => optional($bundle->owner)->public_name ?? 'Onbekend',
    'Downloads' => $bundle->downloads_count,
    'Totale grootte' => Str::filesize($bundle->total_size),
    'Aangemaakt op' => $bundle->created_at->isoFormat('D MMM Y, HH:mm (z)'),
    'Gepubliceerd op' => $bundle->published_at->isoFormat('D MMM Y, HH:mm (z)'),
];
@endphp

@push('files.download-class', 'btn ')
@push('files.download-class', $bundleMedia->isEmpty() ? 'btn--disabled' : 'btn--brand')

@section('two-col.right')
    {{-- File name --}}
    <h1 class="text-3xl font-title mb-4">{{ $bundle->title }}</h1>

    {{-- Description --}}
    <p class="text-gray-primary-1 mb-4">{{ $description }}</p>

    {{-- Download all --}}
    <a class="@stack('files.download-class')" href="{{ route('files.download', compact('bundle')) }}">Alles downloaden</a>

    {{-- Data --}}
    <dl class="my-8 py-8 border-gray-secondary-3 border-t border-b flex flex-row flex-wrap row">
        @foreach ($properties as $label => $value)
        <dt class="col w-1/3 flex-none mb-2 font-bold">{{ $label }}</dt>
        <dd class="col w-2/3 flex-none mb-2 text-sm font-gray-primary-1">{{ $value }}</dd>
        @endforeach
    </dl>

    {{-- Back link --}}
    <a href="{{ route('files.category', ['category' => $bundle->category]) }}" class="inline-block p-4 mb-4 no-underline p-4 text-sm">
        @svg('chevron-left', 'mr-2')
        Terug naar {{ $bundle->category->title }}
    </a>
@endsection

{{-- Files --}}
@section('two-col.left')
    <div class="file-set file-set--inline">
    {{-- Files --}}
    @forelse ($bundleMedia as $file)
        @include('files.bits.file-item', compact('file'))
    @empty
        <div class="file-set__empty-notice">
            <h2 class="file-set__empty-notice-title">Lege bundel</h2>
            <p class="file-set__empty-notice-body">Deze bundel bevat geen bestanden</p>
        </div>
    @endforelse
    </div>
@endsection
