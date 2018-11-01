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
        <tr>
            <td>
                <a href="{{ route('admin.files.show', ['file' => $file]) }}">
                    {{ $file->display_title }}
                </a>
            </td>
            <td>{{ optional($file->owner)->name ?? '–' }}</td>
            <td>
                <a href="{{ $file->url }}" class="btn btn-primary btn-sm">Bekijk op site</a>
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
