@extends('layout.main')

@section('content')
<x-title>
    <h1>Gumbo's Grote Galerij™</h1>

    <x-slot name="subtitle">
      Lekker met je smoel in het album
    </x-slot>
</x-title>

<div class="container">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
    @forelse ($albums as $album)
      <x-gallery.album-tile :album="$album" />
    @empty
      <div class="border-2 border-gray-200 rounded-lg p-8 text-center col-span-4">
        <p class="text-gray-400 text-4xl">
          Er zijn nog geen albums
        </p>
      </div>
    @endforelse
  </div>
</div>

{{-- @can('create', App\Models\Gallery\Album::class) --}}
<div class="container mt-4">
  <div class="flex flex-row gap-4">
    <a href="{{ route('gallery.album.create') }}" class="btn btn--small btn-brand flex items-center">
      <x-icon icon="solid/plus" class="h-4 mr-2" />
      Nieuw album
    </a>
  </div>
</div>
{{-- @endcan --}}
@endsection
