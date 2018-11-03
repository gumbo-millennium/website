@extends('admin.layout.default')

@section('content')
<header class="admin__header">
    <h1 class="admin__title">Documentbeheer – {{ $category->title }}</h1>
    <a href="{{ route('admin.files.index') }}">« Terug naar overzicht</a>
</header>

@if ($category !== null)
<aside role="complementary" class="my-2 col-sm-12">
    @include('admin.files.upload-form', [
        'url' => route('admin.files.upload', ['category' => $category]),
        'category' => $category
    ])
</aside>
@endif

<h2>Documenten in {{ $category->title }}</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Titel</th>
            <th>Uploader</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($files as $file)
        @php
            $publishIcon = $file->public ? 'fa-bell-slash' : 'fa-bell';
            $publishLabel = $file->public ? 'verbergen' : 'publiceren';
        @endphp
        <tr>
            <td>
                <a href="{{ route('admin.files.show', ['file' => $file]) }}">
                    {{ $file->display_title }}
                </a>

                {{-- Forms --}}
                <form id="file-public-{{ $file->id }}" class="d-none" action="{{ route('admin.files.publish', [
                    'file' => $file,
                    'category' => $category
                ]) }}" method="POST">
                    @method('PATCH')
                    @csrf
                    <input type="hidden" name="public" value="{{ $file->public ? '0' : '1' }}" />
                </form>
                <form id="file-delete-{{ $file->id }}" class="d-none" action="{{ route('admin.files.delete', [
                    'file' => $file,
                    'category' => $category
                ]) }}" method="POST">
                    @method('DELETE')
                    @csrf
                </form>
            </td>
            <td>{{ optional($file->owner)->name ?? '–' }}</td>
            <td class="text-right">
                {{-- view file link --}}
                @if ($file->public)
                <a href="{{ $file->url }}" class="btn btn-outline-primary btn-sm" title="bekijk op site">
                    <i class="fas fa-external-link-alt fa-fw"></i>
                    <span class="sr-only">bekijk op site</span>
                </a>
                @endif

                {{-- file actions --}}
                <div class="btn-group" role="group" aria-label="Basic example">
                    @can('publish', $file)
                    <button type="submit" form="file-public-{{ $file->id }}" class="btn btn-outline-secondary btn-sm" title="{{ $publishLabel }}">
                        <i class="fas {{ $publishIcon }} fa-fw"></i>
                        <span class="sr-only">{{ $publishLabel }}</span>
                    </button>
                    @endcan
                    {{-- Update link --}}
                    @can('update', $file)
                    <a href="{{ route('admin.files.edit', ['file' => $file]) }}" class="btn btn-outline-secondary btn-sm" title="bewerken">
                        <i class="fas fa-pencil-alt fa-fw"></i>
                        <span class="sr-only">bewerken</span>
                    </a>
                    @endcan
                    {{-- Delete --}}
                    @can('delete', $file)
                    <a href="{{ route('admin.files.edit', ['file' => $file]) }}" class="btn btn-outline-secondary btn-sm" title="verwijderen">
                        <i class="fas fa-trash-alt fa-fw"></i>
                        <span class="sr-only">verwijderen</span>
                    </a>
                    @endcan
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3">
                <div class="alert alert-info">Geen bestanden in deze categorie</div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $files->links() }}
</div>
@endsection
