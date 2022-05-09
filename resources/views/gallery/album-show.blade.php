@extends('layout.main')

@section('content')
<x-title>
    <h1>Album {{ $album->name }}</h1>

    <x-slot name="subtitle">
      @if ($album->photos->count() > 0)
        {{ $album->photos_count ?? $album->photos->count() }} foto's, genomen vanaf {{
        $album->photos->min('taken_at')?->isoFormat('DD MMMM YYYY') }}
      @else
        Leeg album
      @endif
    </x-slot>
</x-title>

<div class="bg-gray-100 mb-4">
  <div class="container py-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    @if ($album->activity)
        <div class="flex items-center">
          <x-icon icon="calendar-alt" class="h-4 mr-4" />
          <span>Hoort bij <a href="{{ route('activity.show', $album->activity) }}">{{ $album->activity->name }}</a></span>
        </div>
    @elseif ($album->user)
      <div class="flex items-center">
        <x-icon icon="solid/user" class="h-4 mr-4" />
        <span>Gemaakt door <strong>{{ $album->user->name }}</strong></span>
      </div>
    @endif

    <div class="flex items-center">
      <x-icon icon="solid/lightbulb" class="h-4 mr-4" />
      <span>Aangemaakt op <strong>{{ $album->created_at->isoFormat('d MMM YYYY') }}</strong></span>
    </div>

    <div class="flex items-center">
      <x-icon icon="solid/pencil-alt" class="h-4 mr-4" />
      <span>Laatst bewerkt op <strong>{{ $album->updated_at->isoFormat('d MMM YYYY') }}</strong></span>
    </div>
  </div>
</div>

{{-- @canany(['update', 'upload'], $album) --}}
<div class="container mb-4">
  <div class="flex flex-row justify-end gap-4">
    {{-- @can('upload', $post) --}}
    <a href="{{ route('gallery.album.upload', $album) }}" class="btn btn--small btn-primary flex items-center">
      <x-icon icon="solid/upload" class="h-4 mr-2" />
      Upload foto's
    </a>
    {{-- @endcan --}}

    {{-- @can('update', $album) --}}
      <a href="{{ route('gallery.album.edit', $album) }}" class="btn btn--small btn-brand flex items-center">
        <x-icon icon="solid/pencil-alt" class="h-4 mr-2" />
        Bewerk album
      </a>
    {{-- @endcan --}}
  </div>
</div>
{{-- @endcanany --}}

<div class="container">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse ($album->photos as $photo)
      <x-gallery.photo-tile :photo="$photo" />
    @empty
      <div class="border-2 border-gray-200 rounded-lg p-8 text-center col-span-4">
        <p class="text-gray-400 text-4xl">
          Er zijn nog geen foto's in dit album
        </p>
      </div>
    @endforelse
  </div>
</div>
@endsection
