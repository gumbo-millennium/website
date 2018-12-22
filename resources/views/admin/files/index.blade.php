@extends('admin.layout.default')

@section('content')
<header class="admin__header">
    <h1 class="admin__title">@lang('files.titles.name')</h1>
</header>

{{--
@if ($defaultCategory !== null)
@include('admin.files.upload-form', [
    'url' => route('admin.files.upload'),
    'category' => $defaultCategory
])
@endif --}}

<h2>@lang('files.titles.index')</h2>

<div class="table-responsive">
    <table class="table">
        <thead>
            <th scope="col" colspan="2" width="50%">@lang('files.headers.category-name')</th>
            <th scope="col" width="20%">@lang('files.headers.category-count')</th>
            <th scope="col" width="30%">@lang('files.headers.actions')</th>
        </thead>
        <tbody>
            @forelse ($categories as $category)
            <tr>
                <td width="40%">
                    <a href="{{ route('admin.files.browse', ['category' => $category]) }}">
                        <i class="fas fa-fw fa-folder"></i>
                        {{ $category->title }}
                    </a>
                </td>
                <td width="10%">
                    @if ($category->default)
                    <span class="badge badge-brand">default</span>
                    @endif
                </td>
                <td class="text-center" width="20%">
                    {{ $category->files()->count() }}
                </td>
                <td width="30%">
                    <div class="btn-group">
                        <a href="{{ route('admin.files.browse', ['category' => $category]) }}" class="btn btn-outline-primary btn-sm">
                            Bladeren
                        </a>
                        <a href="{{ route('admin.files.category.edit', ['category' => $category]) }}" class="btn btn-outline-secondary btn-sm">
                            Bewerken
                        </a>
                        <a href="{{ route('admin.files.category.remove', ['category' => $category]) }}" class="btn btn-outline-secondary btn-sm">
                            Verwijderen
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-muted text-center">
                    @lang('files.messages.no-categories')
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">
                    <a href="{{ route('admin.files.category.create') }}">
                        <i class="fas fa-fw fa-plus" aria-label="Plus symbol"></i>
                        @lang('files.actions.add-category')
                    </a>
                </td>
                <td class="text-center">
                    {{ $totalFiles }}
                </td>
                <td>
                    &nbsp;
                </td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection
